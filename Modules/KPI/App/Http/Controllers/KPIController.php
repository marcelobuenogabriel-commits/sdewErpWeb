<?php

namespace Modules\KPI\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\KPI\App\Models\KPI;
use Carbon\Carbon;
use Modules\WebService\App\Http\Controllers\WebServiceSeniorXController;
use Modules\WebService\App\Services\SeniorSoapService;
use Illuminate\Support\Facades\Gate;

class KPIController extends Controller
{
    protected $table;
    
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            if (Gate::any(['Administrador', 'Graficos RH'])) {
                return $next($request);
            }

            return redirect('/home')->with('error', 'Você não tem permissão para acessar esta área.');
        });

        $this->table = new KPI();
    }

    public function SalaryComparison()
    {

        $request = request();
        $empresa = $request->input('empresa', null);

        $list = $this->table->getHeadcount($empresa);
       
        $lastPerColaborador = collect($list)
            ->sortByDesc('USU_MESANO')
            ->unique('NUMCAD')
            ->values()
            ->all();

        foreach ($lastPerColaborador as $key => $item) {
            if ($item->SalarioAtual == 0 || is_null($item->SalarioAtual)) {
                $item->SalarioAtual = $item->SalarioAnterior;
                $lastPerColaborador[$key]->PercentualReajuste = 0;
            } else if ($item->SalarioAnterior == 0 || is_null($item->SalarioAnterior)) {
                $lastPerColaborador[$key]->PercentualReajuste = 0;
            } else {
                $lastPerColaborador[$key]->PercentualReajuste = ($item->AjusteSalarial -1) * 100;
            }
        }

        return view('kpi::rh.salarycomparison', [
            'title' => 'Comparativo de Salários',
            'description' => 'Análise de reajustes salariais',
        ])->with('salarios', $lastPerColaborador);
    }

    public function SystemsComparison()
    {

        $request = request();
        $empresa = $request->input('empresa', null);

        $listaComparacao = [];
       
        $baseToCompare = $this->table->getHeadcount($empresa);

        $maxDate = max(array_column($baseToCompare, 'USU_MESANO'));

        /**   Monta o Array do último mês vigente */
        $extractPeriod = function ($item) use ($maxDate) {
            if ($item->USU_MESANO == $maxDate) {
                return $item;
            } 

            return null;
        };
        /**   Fim do Array do último mês vigente */

        /**   Monta o Array de Totais por Centro de Custo e Período */
        foreach ($baseToCompare as $item) {
            $period = $extractPeriod($item);
           
            if ($period === null) {
                continue; 
            }

            if ($period->USU_CCUDES !== $period->USU_CODCCU) {
                $listaComparacao[$period->NOMFUN] = $period;
            }
            
        }
        /**   Fim do Array de Totais por Centro de Custo e Período */

        $this->divergenciaSistemas($empresa, $maxDate);

        return view('kpi::rh.systemcomparison', [
            'title' => 'Controladoria x Recursos Humanos',
            'description' => 'Comparação entre sistemas',
        ])->with('comparacao', $listaComparacao);
    }

    private function divergenciaSistemas($empresa, $maxDate)
    {
        return $this->table->consultaDivergenciaSistemas($empresa, $maxDate);
    }

    public function Headcount()
    {
        $totaisPorPeriodo = [];
        $totaisDesligadosPorPeriodo = [];
        $totaisContratadosPorPeriodo = [];
        $totalPorCentroCusto = [];
        $totalVagasPorEmpresa = [];

        $qtdVagas = 0;
        $log = '';

        $request = request();
        $empresa = $request->input('empresa', null);

        $webServicePainel = new WebServiceSeniorXController();
        $vagasPainel = $webServicePainel->solicitacoesVagas();

        $headcount = $this->table->getHeadcount($empresa);

        
        /**   Pega o ano fiscal atual e monta a data para utilizar de filtro */
        $anoFiscalAtual = Carbon::now()->month >= 4 ? Carbon::now()->year  : Carbon::now()->year-1;
        $mesInicial = '04/' . $anoFiscalAtual;

        /** Consulta no Painel SeniorX e busca as Solicitações de vagas em Aberto */
        foreach ($vagasPainel['flowProcesses'] as $vaga) {
            $idPosto = array_search('WORKSTATION_GROUP', array_column($vaga['detail'], 'field'));
            $idCompany = array_search('COMPANY', array_column($vaga['detail'], 'field'));
            $nomEmp = $vaga['detail'][$idCompany]['value'] ?? 'N/A';
            $posto = $vaga['detail'][$idPosto]['value'] ?? 'N/A';
            $centroCusto = $this->table->consultaPostoTrabalho(substr($posto, 0, 4))->first()->usu_codccu ?? 'N/A';

            $vagas[] = [
                'codEmp' => $nomEmp == 'KNAPP' ? 1 : 2,
                'empresa' => $nomEmp,
                'posto' => $posto ? substr($posto, 0, 4) : 'N/A',
                'centroCusto' => $centroCusto,
            ];
        }
        
        foreach ($vagas as $item) {
            $cc = $item['centroCusto'];
            $emp = $item['codEmp'];

            if (empty(trim($item['centroCusto'])) && ($empresa == $emp || empty($empresa))) {
                $log = "Centro de custo do posto de trabalho {$item['posto']} da Empresa {$item['empresa']} não cadastrado!";
            }

            if ($empresa == $emp) {
                if (!isset($totalVagasPorEmpresa[$emp])) {
                    $totalVagasPorEmpresa[$emp] = [
                        'total' => 0,
                        'ccus' => []
                    ];
                }

                $totalVagasPorEmpresa[$emp]['total']++;

                if (!in_array($item['empresa'], $totalVagasPorEmpresa[$emp]['ccus'])) {
                    $totalVagasPorEmpresa[$emp]['ccus'][] = $item['centroCusto'];
                }

                $qtdVagas += 1;
            } else if ($empresa === null) {
                if (!isset($totalVagasPorEmpresa[$emp])) {
                    $totalVagasPorEmpresa[$emp] = [
                        'total' => 0,
                        'ccus' => []
                    ];
                }

                $totalVagasPorEmpresa[$emp]['total']++;

                if (!in_array($item['empresa'], $totalVagasPorEmpresa[$emp]['ccus'])) {
                    $totalVagasPorEmpresa[$emp]['ccus'][] = $item['centroCusto'];
                }
                $qtdVagas += 1;
            }
        }

        foreach ($totalVagasPorEmpresa as $empId => $data) {
            $ccList = array_filter(array_map(fn($v) => trim((string)$v), $data['ccus']), fn($v) => $v !== '');

            $ccCounts = array_count_values($ccList);

            $totalVagasPorEmpresa[$empId]['cc_counts'] = $ccCounts;
        }
        /** FIM - Consulta no Painel SeniorX e busca as Solicitações de vagas em Aberto */
        
        /**   Monta o Array de Centro de Custo */
        $extractCc = function ($item) {
        
            if (is_array($item)) {
                $desccu = $item['DESCCU'] ?? null;
                $ccudes = $item['USU_CCUDES'] ?? null;
            } else {
                $desccu = $item->DESCCU ?? null;
                $ccudes = $item->USU_CCUDES ?? null;
            }

            if (empty($desccu) && empty($ccudes)) {
                return null;
            }

            $parts = array_filter([$desccu, $ccudes], fn($v) => $v !== null && $v !== '');
            
            return trim(implode(' - ', $parts));
        };
        /**   FIM - do Array de Centro de Custo */

        /**   Monta o Array de Totais por Centro de Custo e Período */
        $extractPeriod = function ($item) {
            $candidates = [];
            if (is_array($item)) {
                $candidates[] = $item['USU_MESANO'] ?? null;
            } elseif (is_object($item)) {
                $candidates[] = $item->USU_MESANO ?? null;
            }

            foreach ($candidates as $c) {
                if (! $c) continue;
               
                if (preg_match('/^\d{4}-\d{2}-\d{2}/', $c)) {
                    try {
                        return Carbon::parse($c)->format('m/Y');
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
            }

            return 'unknown';
        };
        /**   FIM -  do Array de Totais por Centro de Custo e Período */

        /**   Monta o Array de Totais por Centro de Custo e Período */
        foreach ($headcount as $item) {
            $ccCode = $extractCc($item);
            $period = $extractPeriod($item);

            if ($ccCode === null) {
                continue; 
            }

            if (! isset($totaisPorPeriodo[$period])) {
                $totaisPorPeriodo[$period] = [];
            }

            if ($item->SITAFA !== 7) {
                $totaisPorPeriodo[$period][$ccCode] = ($totaisPorPeriodo[$period][$ccCode] ?? 0) + 1;
            } 
            
        }
        /**   Fim do Array de Totais por Centro de Custo e Período */

        /**   Monta o Array de Totais de Desligados por Centro de Custo*/
        $seenDesligados = [];
        foreach ($headcount as $item) {
            $ccCode = $extractCc($item);
            $period = $extractPeriod($item);
            $numCad = is_array($item) ? ($item['NUMCAD'] ?? null) : ($item->NUMCAD ?? null);

            if ($ccCode === null) {
                continue;
            }

            // chave única por centro+empresa+cadastro (ajuste conforme necessidade)
            $uniqueKey = sprintf('%s', $numCad ?? '0');

            if (($item->SITAFA ?? null) == 7) {
                // conta apenas uma vez por cadastro
                if (!isset($seenDesligados[$uniqueKey])) {
                    $totaisDesligadosPorPeriodo[$ccCode] = ($totaisDesligadosPorPeriodo[$ccCode] ?? 0) + 1;
                    $seenDesligados[$uniqueKey] = true;
                }
            } else {
                // garante que a chave exista com zero caso queira exibir todos os centros
                if (!isset($totaisDesligadosPorPeriodo[$ccCode])) {
                    $totaisDesligadosPorPeriodo[$ccCode] = 0;
                }
            }
        }
        /**   Fim do Array de Totais de Desligados por Centro de Custo */

        /**   Monta o Array de Totais de Contratados por Centro de Custo e Período */
        $seenContratados = [];
        foreach ($headcount as $item) {
            $ccCode = $extractCc($item);
            $period = $extractPeriod($item);
            $numCad = is_array($item) ? ($item['NUMCAD'] ?? null) : ($item->NUMCAD ?? null);
            $datAdm = is_array($item) ? ($item['DATADM'] ?? null) : ($item->DATADM ?? null);

            if ($ccCode === null) {
                continue;
            }

            // chave única por centro+empresa+cadastro (ajuste conforme necessidade)
            $uniqueKey = sprintf('%s|%s', $ccCode ?? '0', $numCad ?? '0');

            if ($datAdm >= Carbon::createFromFormat('m/Y', $mesInicial)->startOfMonth()->toDateString()) {
                // conta apenas uma vez por cadastro
                if (!isset($seenContratados[$uniqueKey])) {
                    $totaisContratadosPorPeriodo[$ccCode] = ($totaisContratadosPorPeriodo[$ccCode] ?? 0) + 1;
                    $seenContratados[$uniqueKey] = true;
                }
            } else {
                // garante que a chave exista com zero caso queira exibir todos os centros
                if (!isset($totaisContratadosPorPeriodo[$ccCode])) {
                    $totaisContratadosPorPeriodo[$ccCode] = 0;
                }
            }
        }
        /**   Fim do Array de Totais de Contratados por Centro de Custo e Período */

        // monta array final para a view
        $arrayHeadcount = [
            'centroCusto' => array_keys($totaisPorPeriodo[array_key_first($totaisPorPeriodo)] ?? []),
            'totaisPorPeriodo' => $totaisPorPeriodo,
            'quantitidadeSetorTotal' => array_sum(array_map('array_sum', $totaisPorPeriodo)),
            'totaisDesligados' => $totaisDesligadosPorPeriodo,
            'totaisContratados' => $totaisContratadosPorPeriodo,
            'vagasAbertas' => $totalVagasPorEmpresa ?? [],
            'totalVagas' => $qtdVagas,
            'empresaSelecionada' => $empresa,
            'logError' => $log
        ];

        return view('kpi::rh.headcount', [
            'title' => 'Headcount',
            'description' => 'Por Centro de Custo',
        ])->with('headcount', $arrayHeadcount);
    }
}
