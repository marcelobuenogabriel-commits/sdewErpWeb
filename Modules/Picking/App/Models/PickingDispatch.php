<?php

namespace Modules\Picking\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Picking\Database\factories\PickingDispatchFactory;

class PickingDispatch extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): PickingDispatchFactory
    {
        //return PickingDispatchFactory::new();
    }

    public function findLastMov()
    {
        return DB::table('USU_TMOVDG')
        ->max('USU_CODMOV');
    }

    public function findMovByCodMov($codmov)
    {
        return DB::table('USU_TMOVDG')
            ->select('USU_CODMOV as CODMOV')
            ->where('USU_CODMOV', '=', $codmov)
            ->get()
            ->first();
    }

    public function getQtdTotItem($codSep, $ccuIni)
    {
        return DB::table('USU_TKAEPPO')
            ->select(
                'USU_CODEMP as CODEMP',
                        'USU_NUMPRO as NUMPRO',
                        'USU_CODPCK as CODPCK',
                        'USU_UNIMED as UNIMED',
                        'USU_NUMLT as NUMLT',
                        DB::raw('SUM(USU_QTDPCK) as QTDPCK'))
            ->where('USU_CODSEP', '=', $codSep)
            ->where('USU_CCUINI', '=', $ccuIni)
            ->where('USU_SITPCK', '=', 6)
            ->groupBy(
                'USU_CODEMP',
                        'USU_NUMPRO',
                        'USU_TOPSL',
                        'USU_CODPCK',
                        'USU_UNIMED',
                        'USU_NUMLT')
            ->get()
            ->first();
    }

    /*
     * Query's de UPDATE
     */
    public function closeOrder($codEmp, $numPro, $codSep, $date, $hour, $codUsu)
    {
        return DB::table('USU_TKAEPPO')
            ->where('USU_CODEMP', '=', $codEmp)
            ->where('USU_NUMPRO', '=', $numPro)
            ->where('USU_CODSEP', '=', $codSep)
            ->where('USU_SITPCK', '=', 6)
            ->update([
                'USU_SITPCK' => 4,
                'USU_USUFIN' => $codUsu,
                'USU_DATFIN' => $date,
                'USU_HORFIN' => $hour
            ]);
    }

    /*
     * Query's de Insert
     */
    public function insertNewMovi($numPro, $today, $newMov, $codUsu)
    {
        return DB::table('USU_TMOVDG')
            ->insert([
                'USU_CODEMP' => 1,
                'USU_CODFIL' => 1,
                'USU_CODMOV' => $newMov,
                'USU_TIPMOV' => 2,
                'USU_NUMPRO' => $numPro,
                'USU_DESMOV' => 'Area Sheet Estoque',
                'USU_USUGER' => $codUsu,
                'USU_DATGER' => $today,
                'USU_HORGER' => 834,
                'USU_SITMOV' => 2]);
    }

    public function insertItemMov($codEmp, $codMov, $codPro, $uniMed, $qtdMov, $numLt, $codUsu, $date, $hour)
    {
        return DB::table('USU_TMOVITE')
            ->insert([
                'USU_CODEMP' => $codEmp,
                'USU_CODFIL' => 1,
                'USU_CODMOV' => $codMov,
                'USU_UNIMED' => $uniMed,
                'USU_QTDUNI' => $qtdMov,
                'USU_NUMORP' => 0,
                'USU_CODVER' => 0,
                'USU_CODFAM' => $numLt,
                'USU_CODMNR' => $numLt,
                'USU_CODSTA' => NULL,
                'USU_USUGER' => $codUsu,
                'USU_DATGER' => $date,
                'USU_HORGER' => $hour,
                'USU_TIPITE' => 1,
                'USU_MATMON' => 0,
                'USU_CODID' => $codPro,
                'USU_PARAM' => 'Item Oriundo do Estoque.'
                ]);
    }
}
