<?php

namespace Modules\Picking\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Picking\Database\factories\PickingCPartsFactory;

class PickingCParts extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): PickingCPartsFactory
    {
        //return PickingCPartsFactory::new();
    }

    public function getQtdTotItem($codSep, $ccuIni)
    {
        return DB::table('USU_TKAEPPO')
            ->select(
                'USU_CODEMP as CODEMP',
                        'USU_NUMPRO as NUMPRO',
                        'USU_CODPCK as CODPCK',
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
    public function closeOrder($codEmp, $numPro, $codSep, $codUsu, $date, $hour)
    {
        return DB::table('USU_TKAEPPO')
            ->where('USU_CODEMP', '=', $codEmp)
            ->where('USU_NUMPRO', '=', $numPro)
            ->where('USU_CODSEP', '=', $codSep)
            ->where('USU_TOPSL', '=', 'C-Parts')
            ->update([
                'USU_SITPCK' => 4,
                'USU_USUFIN' => $codUsu,
                'USU_DATFIN' => $date,
                'USU_HORFIN' => $hour
            ]);
    }
}
