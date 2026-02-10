<?php

namespace Modules\Producao\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Producao\Database\factories\TKAEPDGFactory;

class TKAEPDG extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): TKAEPDGFactory
    {
        //return TKAEPDGFactory::new();
    }

    public function checkmotor($numorp)
    {
        return DB::table('USU_TKAEPPR')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_CODFIL', '=', 1)
            ->where('USU_NUMORP', '=', $numorp)
            ->where(function ($query) {
                $query->where('USU_DESPER', 'like', '%STPA%')
                    ->orWhere('USU_DESPER', 'like', '%MRA3%')
                    ->orWhere('USU_DESPER', 'like', '%MRA1%');
            })
            ->Where('USU_DESPER', 'like', '%VF%')
            ->get()
            ->last();
    }

    public function checksensormotor($numorp)
    {
        return DB::table('USU_TKAEPLM')
            ->selectRaw('SUM(USU_TKAEPLM.USU_QTDLMA) as QTDSENSOR')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_CODFIL', '=', 1)
            ->where('USU_NUMORP', '=', $numorp)
            ->where('USU_CODID', 'like', '10326197%')
            ->groupBy('USU_CODVER')
            ->get()->last();
    }

    public function checkvalvulas($numorp)
    {
        return DB::table('USU_TKAEPPR')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_CODFIL', '=', 1)
            ->where('USU_NUMORP', '=', $numorp)
            ->where('USU_CODID', 'like', '%STPA%')
            ->where('USU_CODID', 'like', '%VF%')
            ->get()->last();
    }

    public function getOrdensMain($numorp)
    {
        $projeto = DB::table('usu_tkaepdg')
            ->select('usu_numpro')
            ->where('usu_numorp', '=', $numorp)
            ->get()->last();

        return DB::table('usu_tkaepdg')
            ->select('estatisticas.min_orp', 'estatisticas.max_orp')
            ->fromSub(function ($query) use ($numorp, $projeto) {
                $query->select('usu_numorp')
                    ->from('usu_tkaepdg')
                    ->where('usu_codniv', 1)
                    ->where('usu_numpro', $projeto->usu_numpro)
                    ->where('usu_numorp', '>=', $numorp)
                    ->where('usu_sitorp', '<>', 3)
                    ->orderBy('usu_numorp')
                    ->limit(2);
            }, 'dados')
            ->crossJoinSub(function ($query) use ($numorp, $projeto) {
                $query->selectRaw('MIN(usu_numorp) AS min_orp, MAX(usu_numorp) AS max_orp')
                    ->from('usu_tkaepdg')
                    ->where('usu_codniv', 1)
                    ->where('usu_numpro', $projeto->usu_numpro)
                    ->where('usu_numorp', '>=', $numorp)
                    ->where('usu_numorp', '<>', 3);
            }, 'estatisticas')
            ->limit(1)
            ->get();
    }

    public function getOrdensList($numorpini, $numorpfim){
        $projeto = DB::table('usu_tkaepdg')
            ->select('usu_numpro')
            ->where('usu_numorp', '=', $numorpini)
            ->get()->last();

        return DB::table('usu_tkaepdg')
            ->select('USU_TKAEPDG.usu_numorp', 'USU_TKAEPDG.usu_topsl', 'USU_TKAEPDG.usu_codniv', 'USU_TKAEPPR.usu_codid')
            ->join("usu_tkaeppr", function($join) {
                $join->on('usu_tkaepdg.usu_numorp', '=', 'usu_tkaeppr.usu_numorp')
                ->on('usu_tkaepdg.usu_codemp', '=', 'usu_tkaeppr.usu_codemp')
                ->on('usu_tkaepdg.usu_codfil', '=', 'usu_tkaeppr.usu_codfil')
                ->on('usu_tkaepdg.usu_seqorp', '=', 'usu_tkaeppr.usu_seqorp')
                ->on('usu_tkaepdg.usu_codver', '=', 'usu_tkaeppr.usu_codver')
                ->where('usu_tkaeppr.usu_seqper', '=', 1)
                ->where('usu_tkaepdg.usu_sitorp', '<>', 3);
            })
            ->whereBetween('usu_tkaepdg.usu_numorp', [$numorpini, $numorpfim])
            ->where('usu_tkaepdg.usu_numpro', $projeto->usu_numpro)
            ->get();

    }
}
