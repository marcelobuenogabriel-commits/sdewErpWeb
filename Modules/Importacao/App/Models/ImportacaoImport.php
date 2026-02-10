<?php

namespace Modules\Importacao\App\Models;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ImportacaoImport implements ToCollection, WithStartRow
{
    /**
     * Recebe uma Collection de linhas (cada linha como array associativo pelo nome da coluna)
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            
            $quantidadeEntregue = $row[7] ?? null;
            $produto = $row[10] ?? null;
            $quantidadeEntregue = null;

            if ($quantidadeEntregue == null || $produto == null) {
                break;
            }
        }
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        // Inicia a importação a partir da segunda linha
        return 2;
    }
}