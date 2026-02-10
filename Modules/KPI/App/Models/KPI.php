<?php

namespace Modules\KPI\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\KPI\Database\factories\KPIFactory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KPI extends Model
{

    protected $anoFiscalAtual;

    protected $dataInicial;

    protected $dataFinal;
    
    public function __construct()
    {
        /**   Pega o ano fiscal atual e monta a data para utilizar de filtro */
        $this->anoFiscalAtual = Carbon::now()->month >= 4 ? Carbon::now()->year  : Carbon::now()->year-1;
        $this->dataInicial = '01/04/' . $this->anoFiscalAtual;
        $this->dataFinal = '31/03/' . ($this->anoFiscalAtual +1);
    }
    
    public function consultaPostoTrabalho($codPosto)
    {
        return DB::table('LINKEDSERVERVET.VETORH.DBO.R017POS')
            ->where('ESTPOS', 2)
            ->where('POSTRA', $codPosto)
            ->get();
    }

    public function consultaDivergenciaSistemas($empresa, $mesAno)
    {
        $sql = "
            SELECT FUN.NOMFUN, FUN.CODCCU, FUN.NUMEMP
            FROM LINKEDSERVERVET.VETORH.DBO.R034FUN FUN 
            LEFT JOIN USU_TRATMOV MOV 
                ON MOV.USU_CODEMP = FUN.NUMEMP
                AND MOV.USU_CODCCU = FUN.CODCCU
                AND MOV.USU_SEQDST = 3 
                AND MOV.USU_MESANO = '{$mesAno}'
            WHERE
                FUN.TIPCOL = 1
                AND FUN.SITAFA <> 7
                AND MOV.USU_SEQDST IS NULL
                AND FUN.TABORG = 2";

        $bindings = [];

        // adiciona filtro por empresa somente se informado
        if (!is_null($empresa) && $empresa !== '') {
            $sql .= " AND USU_CODEMP = ? ";
            $bindings[] = (int) $empresa;
        }

         return DB::select($sql, $bindings);
    }

    public function getHeadcount($empresa = null)
    {
        $bindings = [];

        // monta SQL base
        $sql = '
            SELECT
                FUN.NUMEMP,
                MOV.USU_MESANO,
                MOV.USU_CCUDES,
                POS.USU_CODCCU,
                CCU.DESCCU,
                FUN.CODFOR,
                FUN.NUMCAD,
                FUN.CODCCU,
                FUN.NOMFUN,
                FUN.SITAFA,
                FUN.DATAFA,
                FUN.DATADM,
                POS.DESRED,
                MOV.USU_CODEMP,
                POS.POSTRA,
                (SELECT TOP 1 VALSAL FROM LINKEDSERVERVET.VETORH.DBO.R038HSA WHERE numemp = FUN.NUMEMP AND datalt < ? and numcad = FUN.NUMCAD ORDER BY datalt DESC) AS SalarioAnterior,
                (SELECT top 1 valsal FROM LINKEDSERVERVET.VETORH.DBO.R038HSA WHERE numemp = FUN.NUMEMP AND datalt BETWEEN ? AND ? and numcad = FUN.NUMCAD ORDER BY datalt DESC) as SalarioAtual,
                ((SELECT top 1 valsal FROM LINKEDSERVERVET.VETORH.DBO.R038HSA WHERE numemp = FUN.NUMEMP AND datalt BETWEEN ? AND ? and numcad = FUN.NUMCAD ORDER BY datalt DESC)/(SELECT TOP 1 VALSAL FROM LINKEDSERVERVET.VETORH.DBO.R038HSA WHERE numemp = FUN.NUMEMP AND datalt < ? and numcad = FUN.NUMCAD ORDER BY datalt DESC)) as AjusteSalarial
            FROM 
                USU_TRATMOV MOV
                INNER JOIN LINKEDSERVERVET.VETORH.DBO.R034FUN FUN 
                    ON MOV.USU_CCUORI = FUN.CODCCU
                INNER JOIN LINKEDSERVERVET.VETORH.DBO.R017POS POS 
                    ON FUN.ESTPOS = POS.ESTPOS 
                    AND FUN.POSTRA = POS.POSTRA
                INNER JOIN LINKEDSERVERVET.VETORH.DBO.R044MOV MOVRH 
                    ON MOVRH.NUMEMP = FUN.NUMEMP 
                    AND MOVRH.NUMCAD = FUN.NUMCAD
                INNER JOIN LINKEDSERVERVET.VETORH.DBO.R044CAL CAL 
                    ON MOVRH.NUMEMP = CAL.NUMEMP 
                    AND MOVRH.CODCAL = CAL.CODCAL 
                    AND CAL.PERREF = MOV.USU_MESANO
                INNER JOIN E044CCU CCU 
                    ON MOV.USU_CODEMP = CCU.CODEMP 
                    AND MOV.USU_CCUDES = CCU.CODCCU
            WHERE 
                USU_SEQDST IN (2,3)
                AND USU_MESANO BETWEEN ? AND ?
                AND FUN.TIPCOL = 1
        ';

        $bindings[] = Carbon::createFromFormat('d/m/Y', $this->dataInicial)->toDateString();
        $bindings[] = Carbon::createFromFormat('d/m/Y', $this->dataInicial)->toDateString();
        $bindings[] = Carbon::createFromFormat('d/m/Y', $this->dataFinal)->toDateString();

        
        $bindings[] = Carbon::createFromFormat('d/m/Y', $this->dataInicial)->toDateString();
        $bindings[] = Carbon::createFromFormat('d/m/Y', $this->dataFinal)->toDateString();
        $bindings[] = Carbon::createFromFormat('d/m/Y', $this->dataInicial)->toDateString();

        $bindings[] = Carbon::createFromFormat('d/m/Y', $this->dataInicial)->toDateString();
        $bindings[] = Carbon::createFromFormat('d/m/Y', $this->dataFinal)->toDateString();

        // adiciona filtro por empresa somente se informado
        if (!is_null($empresa) && $empresa !== '') {
            $sql .= " AND USU_CODEMP = ? ";
            $bindings[] = (int) $empresa;
        }

        $sql .= '
            GROUP BY 
                FUN.NUMEMP,
                MOV.USU_MESANO,
                MOV.USU_CCUDES,
                POS.USU_CODCCU,
                CCU.DESCCU,
                FUN.CODFOR,
                FUN.NUMCAD,
                FUN.CODCCU,
                FUN.NOMFUN,
                FUN.SITAFA,
                FUN.DATAFA,
                FUN.DATADM,
                POS.DESRED,
                MOV.USU_CODEMP,
                POS.POSTRA
            ORDER BY 
                MOV.USU_CCUDES,
                NOMFUN ASC,
                SITAFA ASC
        ';
        
        return DB::select($sql, $bindings);
    }
}
