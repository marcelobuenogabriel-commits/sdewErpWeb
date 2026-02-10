<?php

namespace App\Jobs;

use App\Notifications\JobFinalizadoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AgrupaOrdemProducaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 20;
    public $timeout = 1600;

    protected $xNumPro;
    protected $xCodFam;
    protected $xCodSta;
    protected $xCodIdPr;

    public function __construct($xNumPro, $xCodFam, $xCodSta = null, $xCodIdPr = null)
    {
        $this->xNumPro = $xNumPro;
        $this->xCodFam = $xCodFam;
        $this->xCodSta = $xCodSta;
        $this->xCodIdPr = $xCodIdPr;
    }

    public function handle(): void
    {
        $startTime = microtime(true);
        $jobId = uniqid('job_', true);

        $logger = Log::channel('single'); // Usar canal padrão

        // Lock manual para evitar execução paralela
        $lockKey = "agrupa_ordem_global_lock";

        if (Cache::has($lockKey)) {
            $lockInfo = Cache::get($lockKey);
            $logger->warning("[$jobId] ⚠️ JOB JÁ RODANDO - Ignorando execução duplicada", [
                'running_job' => $lockInfo['job_id'] ?? 'unknown',
                'started_at' => $lockInfo['started_at'] ?? 'unknown'
            ]);
            return;
        }

        Cache::put($lockKey, [
            'job_id' => $jobId,
            'started_at' => now()->toDateTimeString()
        ], now()->addSeconds(1800));

        try {
            $logger->info("[$jobId] JOB INICIADO", [
                'xNumPro' => $this->xNumPro,
                'xCodFam' => $this->xCodFam,
                'xCodSta' => $this->xCodSta ?? 'N/A',
                'attempt' => $this->attempts()
            ]);

            $query = DB::table('USU_VKAEPGRU')
                ->where('usu_numpro', $this->xNumPro)
                ->where('usu_codfam', $this->xCodFam);

            if (!empty($this->xCodSta)) {
                $query->where('usu_codsta', $this->xCodSta);
            }

            if (!empty($this->xCodIdPr)) {
                $query->where('usu_codid', $this->xCodIdPr);
            }

            $rows = $query->get();
            $totalRows = count($rows);

            $logger->info("[$jobId] Registros encontrados: {$totalRows}");

            if ($totalRows === 0) {
                $logger->warning("[$jobId] Nenhum registro para processar");
                Cache::forget($lockKey);
                return;
            }

            // ⚠️ VALIDAÇÃO CRÍTICA: Verificar coluna USU_NUMORP no banco
            $logger->info("[$jobId] Verificando tipo de dados da coluna USU_NUMORP...");

            $columnInfo = DB::connection('sqlsrv')->select("
                SELECT
                    c.name AS column_name,
                    t.name AS data_type,
                    c.max_length,
                    c.precision,
                    c.scale
                FROM sys.columns c
                INNER JOIN sys.types t ON c.user_type_id = t.user_type_id
                WHERE c.object_id = OBJECT_ID('USU_TKAEPDG')
                  AND c.name = 'USU_NUMORP'
            ");

            if (!empty($columnInfo)) {
                $colInfo = $columnInfo[0];
                $logger->info("[$jobId] Coluna USU_NUMORP: {$colInfo->data_type}({$colInfo->precision},{$colInfo->scale})");

                // Calcular o valor máximo suportado
                $maxValue = pow(10, $colInfo->precision - $colInfo->scale) - 1;
                $logger->info("[$jobId] Valor máximo suportado: {$maxValue}");
            } else {
                // Assumir limite padrão se não conseguir verificar
                $maxValue = 999999999999; // NUMERIC(12,0)
                $logger->warning("[$jobId] Não foi possível verificar tipo da coluna, usando limite padrão: {$maxValue}");
            }

            DB::beginTransaction();

            $processedCount = 0;
            $skippedCount = 0;
            $skippedRows = [];

            foreach ($rows as $index => $row) {
                $rowNum = $index + 1;

                // VALIDAÇÃO: Verificar se minnumorp é numérico e está dentro do limite
                $minnumorpNumeric = is_numeric($row->minnumorp) ? (float)$row->minnumorp : 0;

                if ($minnumorpNumeric > $maxValue) {
                    $logger->warning("[$jobId] ⚠️ PULANDO registro {$rowNum}/{$totalRows}", [
                        'codid' => $row->usu_codid,
                        'minnumorp' => $row->minnumorp,
                        'minnumorp_value' => $minnumorpNumeric,
                        'max_allowed' => $maxValue,
                        'reason' => 'Valor excede capacidade da coluna'
                    ]);

                    $skippedRows[] = [
                        'row' => $rowNum,
                        'codid' => $row->usu_codid,
                        'minnumorp' => $row->minnumorp
                    ];

                    $skippedCount++;
                    continue;
                }

                try {
                    if ($rowNum % 10 == 0 || $rowNum == 1 || $rowNum == $totalRows) {
                        $logger->info("[$jobId] Processando {$rowNum}/{$totalRows}");
                    }

                    DB::statement('EXEC usp_AgrupaOrdemProducao ?, ?, ?, ?, ?, ?', [
                        $row->usu_numpro,
                        $row->usu_codfam,
                        $row->usu_codsta,
                        $row->usu_codid,
                        $row->usu_qtdpertot,
                        $row->minnumorp
                    ]);

                    $processedCount++;

                } catch (\Illuminate\Database\QueryException $e) {
                    $logger->error("[$jobId] ❌ ERRO no registro {$rowNum}/{$totalRows}", [
                        'error' => $e->getMessage(),
                        'codid' => $row->usu_codid,
                        'minnumorp' => $row->minnumorp
                    ]);

                    DB::rollBack();
                    Cache::forget($lockKey);
                    throw $e;
                }
            }

            DB::commit();

            $duration = round(microtime(true) - $startTime, 2);

            $logger->info("[$jobId] ✅ JOB CONCLUÍDO", [
                'total' => $totalRows,
                'processados' => $processedCount,
                'pulados' => $skippedCount,
                'tempo_segundos' => $duration
            ]);

            if ($skippedCount > 0) {
                $logger->warning("[$jobId] Registros pulados por overflow:", [
                    'total_pulados' => $skippedCount,
                    'detalhes' => $skippedRows
                ]);
            }

	    $emails = ['marcelo.gabriel@knapp.com', 'jeferson.souza@knapp.com'];

            Notification::route('mail', $emails)
                ->notify(new JobFinalizadoNotification('Concluído', $this->xNumPro, $this->xCodFam, $this->xCodSta));

        } catch (\Exception $e) {
            $logger->error("[$jobId] ❌ ERRO FATAL", [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ]);

            throw $e;

        } finally {
            Cache::forget($lockKey);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Job AgrupaOrdemProducaoJob falhou permanentemente", [
            'xNumPro' => $this->xNumPro,
            'xCodFam' => $this->xCodFam,
            'error' => $exception->getMessage()
        ]);

        Cache::forget("agrupa_ordem_global_lock");

	$emails = ['marcelo.gabriel@knapp.com', 'jeferson.souza@knapp.com'];

        Notification::route('mail', $mails)
            ->notify(new JobFinalizadoNotification('Falhou', $this->xNumPro, $this->xCodFam, $this->CodSta));
    }
}
