<?php

namespace Modules\Financeiro\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Modules\Financeiro\Database\factories\PedidosFactory;

class Pedidos extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): PedidosFactory
    {
        //return PedidosFactory::new();
    }

    public function getPedidos($codEmp, $numPed = null)
    {
        return DB::table('E120PED as PED')
            ->select(['PED.NUMPED', 'CLI.NOMCLI'])
            ->join('E085CLI AS CLI', function (JoinClause $join) {
                $join->on('PED.CODCLI', '=', 'CLI.CODCLI');
            })
            ->where('PED.CODEMP', '=', $codEmp)
            ->whereRaw('YEAR(PED.DATGER) >= (' . date('Y') . '-3)')
            //->whereRaw('PED.SITPED NOT IN (4,5,9)')
            ->orderBy('PED.NUMPED', 'DESC')
            ->get();
    }

    public function getPedido($codEmp, $codPed)
    {
        return DB::table('E120PED as PED')
            ->selectRaw(
                'SUM(IPD.QTDPED * IPD.PREUNI) as TOTIPD,
                          SUM(ISP.QTDPED * ISP.PREUNI) as TOTISP,
                          CASE
                            WHEN IPD.CODMOE IS NOT NULL THEN IPD.CODMOE
                            WHEN ISP.CODMOE IS NOT NULL THEN ISP.CODMOE
                          END CODMOE,
                          PED.NUMPED,
                          CLI.NOMCLI'
            )
            ->join('E085CLI AS CLI', function (JoinClause $join) {
                $join->on('PED.CODCLI', '=', 'CLI.CODCLI');
            })
            ->leftJoin('E120IPD AS IPD', function (JoinClause $join) {
                $join->on('PED.CODEMP', '=', 'IPD.CODEMP');
                $join->on('PED.NUMPED', '=', 'IPD.NUMPED');
            })
            ->leftJoin('E120ISP AS ISP', function (JoinClause $join) {
                $join->on('PED.CODEMP', '=', 'ISP.CODEMP');
                $join->on('PED.NUMPED', '=', 'ISP.NUMPED');
            })
            ->where('PED.CODEMP', '=', $codEmp)
            ->where('PED.NUMPED', '=', $codPed)
            ->whereRaw('(IPD.SITIPD <> 5 OR ISP.SITISP <> 5)')
            ->groupByRaw('CASE
                            WHEN IPD.CODMOE IS NOT NULL THEN IPD.CODMOE
                            WHEN ISP.CODMOE IS NOT NULL THEN ISP.CODMOE
                          END,
                          PED.NUMPED,
                          CLI.NOMCLI')
            ->get();
    }

    public function getContratos($codEmp, $numPed = null)
    {
        return DB::table('E160CTR as CTR')
            ->select(['CTR.NUMCTR', 'CLI.NOMCLI'])
            ->join('E085CLI AS CLI', function (JoinClause $join) {
                $join->on('CTR.CODCLI', '=', 'CLI.CODCLI');
            })
            ->where('CTR.CODEMP', '=', $codEmp)
            ->where('CTR.SITCTR', '=', 'A')
            ->orderBy('CTR.NUMCTR', 'DESC')
            ->get();
    }

    public function getContrato($codEmp, $numCtr)
    {
        return DB::table('E160CTR as CTR')
            ->select([
                'CVS.CODMOE', 'CVS.CPLCVS', 'CLI.NOMCLI', 'CTR.NUMCTR'
            ])
            ->join('E085CLI AS CLI', function (JoinClause $join) {
                $join->on('CTR.CODCLI', '=', 'CLI.CODCLI');
            })
            ->leftJoin('E160CVS AS CVS', function (JoinClause $join) {
                $join->on('CTR.CODEMP', '=', 'CVS.CODEMP');
                $join->on('CTR.NUMCTR', '=', 'CVS.NUMCTR');
            })
            ->where('CTR.CODEMP', '=', $codEmp)
            ->where('CTR.NUMCTR', '=', $numCtr)
            ->where('CTR.SITCTR', '=', 'A')
            ->get();
    }

    public function consultaValorPedido($codEmp, $codPed)
    {
        return DB::table('E120PED as PED')
            ->selectRaw(
                'SUM(IPD.QTDPED * IPD.PREUNI) as TOTIPD,
                          SUM(ISP.QTDPED * ISP.PREUNI) as TOTISP'
            )
            ->leftJoin('E120IPD AS IPD', function (JoinClause $join) {
                $join->on('PED.CODEMP', '=', 'IPD.CODEMP');
                $join->on('PED.NUMPED', '=', 'IPD.NUMPED');
            })
            ->leftJoin('E120ISP AS ISP', function (JoinClause $join) {
                $join->on('PED.CODEMP', '=', 'ISP.CODEMP');
                $join->on('PED.NUMPED', '=', 'ISP.NUMPED');
            })
            ->where('PED.CODEMP', '=', $codEmp)
            ->where('PED.NUMPED', '=', $codPed)
            ->whereRaw('(IPD.SITIPD <> 5 OR ISP.SITISP <> 5)')
            ->groupBy('IPD.CODMOE', 'ISP.CODMOE', 'IPD.CPLIPD', 'ISP.CPLISP')
            ->get();
    }
}
