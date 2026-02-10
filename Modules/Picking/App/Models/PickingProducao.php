<?php

namespace Modules\Picking\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Picking\Database\factories\PickingProducaoFactory;
use function Laravel\Prompts\select;

class PickingProducao extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): PickingProducaoFactory
    {
        //return PickingProducaoFactory::new();
    }

    /*
     * Query's de Consulta
     */
    public function getQtdTotItem($codSep, $ccuIni)
    {
        return DB::table('USU_TKAEPPO')
            ->select(
                'USU_CODEMP as CODEMP',
                        'USU_NUMPRO as NUMPRO',
                        'USU_INIPRO as INIPRO',
                        'USU_CODPCK as CODPCK',
                        DB::raw('SUM(USU_QTDPCK) as QTDPCK'))
            ->where('USU_CODSEP', '=', $codSep)
            ->where('USU_CCUINI', '=', $ccuIni)
            ->where('USU_SITPCK', '=', 6)
            ->groupBy(
                'USU_CODEMP',
                        'USU_NUMPRO',
                        DB::raw('SUBSTRING(USU_CODFAM,1,6)'),
                        'USU_INIPRO',
                        'USU_TOPSL',
                        'USU_CODPCK')
            ->get()
            ->first();
    }

    public function consultProject($codEmp, $numPro)
    {
        return DB::table('E615PRJ')
            ->select('NUMPRJ')
            ->where('CODEMP', '=', $codEmp)
            ->where('USU_PRJAUS', '=', $numPro)
            ->get()
            ->first();
    }

    public function consultDeposit($codEmp, $numDep)
    {
        return DB::table('E205DEP')
            ->select('CODDEP')
            ->where('CODEMP', '=', $codEmp)
            ->where('CODDEP', '=', $numDep)
            ->get()
            ->first();
    }

    public function consultStock($codEmp, $codPro, $depOri, $qtdMov)
    {
        return DB::table('E210EST')
            ->where('CODEMP', '=', $codEmp)
            ->where('CODPRO', '=', $codPro)
            ->where('CODDEP', '=', $depOri)
            ->where('CODDER', '=', '')
            ->whereRaw('(QTDEST - QTDRES) >= '.$qtdMov)
            ->get()
            ->first();
    }

    public function consultProdDep($codEmp, $codPro, $depDes)
    {
        return DB::table('E210EST')
            ->where('CODEMP', '=', $codEmp)
            ->where('CODPRO', '=', $codPro)
            ->where('CODDEP', '=', $depDes)
            ->where('CODDER', '=', '')
            ->get()
            ->first();
    }

    public function consultProdInfo($codEmp, $codPro)
    {
        return DB::table('E075PRO')
            ->select('UNIMED')
            ->where('CODEMP', '=', $codEmp)
            ->where('CODPRO', '=', $codPro)
            ->get()
            ->first();
    }

    public function getCodProSep($codSep)
    {
        return DB::table('USU_TKAEPPO')
            ->select('USU_CODPCK')
            ->where('USU_CODSEP', '=', $codSep)
            ->get()
            ->first();
    }


    /*
     * Query's de UPDATE
     */
    public function closeOrder($codEmp, $numPro, $codSep, $codUsu, $date, $hour)
    {
        return DB::table('USU_TKAEPPO')
            ->where('USU_CODEMP', '=', $codEmp)
            ->where('USU_NUMPRO', '=', $numPro)
            ->where('USU_CODSEP', '=', $codSep)
            ->update([
                'USU_SITPCK' => 4,
                'USU_USUFIN' => $codUsu,
                'USU_DATFIN' => $date,
                'USU_HORFIN' => $hour
            ]);
    }

    public function rollBackOrder($codSep)
    {
        return DB::table('USU_TKAEPPO')
            ->where('USU_CODDEP', '=', $codSep)
            ->update([
                'USU_SITPCK' => 6,
                'USU_USUFIN' => '',
                'USU_DATFIN' => '',
                'USU_HORFIN' => ''
            ]);
    }

    public function updatePallet($codPro, $codPal)
    {
        return DB::table('USU_T_PALLET')
            ->where('USU_ENDPAL', '=', $codPal)
            ->where('USU_CODPRO', '=', $codPro)
            ->update([
                'USU_ENDBAK' => $codPal,
                'USU_ENDPAL' => ''
            ]);
    }

    /*
     * Query's de Insert
     */
    public function insertProductStock($codEmp, $codPro, $depDes, $date, $hour, $uniMed, $codUsu)
    {
        return DB::insert(
            'INSERT INTO E210EST(
                        CodEmp,CodPro,CodDer,CodDep,DatIni,SalIni,
                        NivDep,UniMed,EstNeg,QtdEst,QtdBlo,QtdRes,
                        QtdRae,QtdOrd,QtdCcl,QtdCfo,EstRep,EstMin,
                        EstMax,EstMid,EstMad,DatCcr,DatCfr,QtdCcr,
                        DatUen,DatUsa,DatVal,IndInv,SitEst,CodMot,
                        UsuGer,DatGer,HorGer,PrzRsu,QtdEmb,EstCap,
                        DatUan,LigEsp
                        )
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $codEmp, "$codPro", '', "$depDes", "$date", 0, 0, "$uniMed", 'N',
                0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                0,  'A', 0, $codUsu, "$date", $hour, 0, 0, 0, 0, 'N'
            ]);
    }

    /*
     * Query's de Delete
     */

}
