<?php

namespace Modules\Producao\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Producao\Database\factories\PLCAPP03Factory;

class PLCAPP03 extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): PLCAPP03Factory
    {
        //return PLCAPP03Factory::new();
    }

    public function deleteop($numorp){
        return DB::connection('adapterApp03')
        ->table('ERP02SENIOR_PLCAPP03')
            ->where('NUMORP', '=', $numorp)
            ->delete();
    }

    public function deletebench($bancada){
        return DB::connection('adapterApp03')
            ->table('ERP02SENIOR_PLCAPP03')
            ->where('BENCH', '=', $bancada)
            ->delete();
    }

    public function inserttest($usu_numorp, $getbench, $qtdmotor, $qtdsensormotor, $qtdvalvula, $qtdsensor){
        $qtdsensortot = $qtdsensormotor + $qtdsensor;
        if(!is_null(session('bench'))) {
            return DB::connection('adapterApp03')->table('ERP02SENIOR_PLCAPP03')
                ->insert([
                    'NUMORP' => $usu_numorp,
                    'BENCH' => $getbench,
                    'QTDMOTOR' => $qtdmotor,
                    'QTDSENSOR' => $qtdsensortot,
                    'QTDVALVULA' => $qtdvalvula
                ]);
        }
    }

    public function gettestsordem($numorp){
        return DB::connection('adapterApp03')->table('ERP02SENIOR_PLCAPP03')
            ->where('NUMORP', '=', $numorp)
            ->get();
    }

    public function selectordem($numorp, $tabela){
        return DB::connection('adapterApp03')->table('Hist_Bancada_' . $tabela)
            ->where('OP_SELECIONADA', '=', $numorp)
            ->whereRaw("CONTADOR_TESTE = (SELECT MAX(CONTADOR_TESTE) FROM Hist_Bancada_". $tabela . " WHERE OP_SELECIONADA = '$numorp')")
            ->get();
    }
}
