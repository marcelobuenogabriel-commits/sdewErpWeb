<?php

namespace Modules\Inventario\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Inventario\Database\factories\InventarioFactory;

class Inventario extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): InventarioFactory
    {
        //return InventarioFactory::new();
    }

    public function findAll()
    {
        return DB::table('E220INV as INV')
            ->select('INV.CODEMP', 'INV.DATINV', 'INV.CODDEP', 'INV.CODUSU', 'INV.DATGER', 'INV.HORGER', 'INV.ULTCON', 'INV.BLOMOV', 'INV.LOTSER', 'INV.INVEMB', 'INV.ACECON',
            'EMP.NOMEMP')
            ->leftJoin('E070EMP as EMP', function($join){
                $join->on('INV.CODEMP', '=', 'EMP.CODEMP');
            })
            ->where('INV.CODEMP', '=', 1)
            ->where('INV.ULTCON', '>', 0)
            ->whereRaw('0 = 0')
            ->orderByRaw('2 ASC OPTION (FAST 1)')
            ->get();
    }


    public function findAllItens($datInv, $codDep, $conInv)
    {
        return DB::table('E220CON as CON')
            ->join('E075PRO as PRO', function($join){
                $join->on('CON.CODEMP', '=', 'PRO.CODEMP');
                $join->on('CON.CODPRO', '=', 'PRO.CODPRO');
            })
            ->join('E075DER as DER', function($join){
                $join->on('CON.CODEMP', '=', 'DER.CODEMP');
                $join->on('CON.CODPRO', '=', 'DER.CODPRO');
                $join->on('CON.CODDER', '=', 'DER.CODDER');
            })
            ->join('E083ORI as ORI', function($join){
                $join->on('PRO.CODEMP', '=', 'ORI.CODEMP');
                $join->on('PRO.CODORI', '=', 'ORI.CODORI');
            })
            ->leftJoin('E210EST as EST', function($join){
                $join->on('CON.CODEMP', '=', 'EST.CODEMP');
                $join->on('CON.CODDEP', '=', 'EST.CODDEP');
                $join->on('CON.CODPRO', '=', 'EST.CODPRO');
            })
            ->where('CON.CODEMP', '=', 1)
            ->where('CON.DATINV', '=', $datInv)
            ->where('CON.CODDEP', '=', $codDep)
            ->where('CON.NUMCON', '=', 0)
            ->where('CON.QTDCON', '=', 0)
            ->whereNull('CON.MOTACE')
            ->whereNotExists(function($query){
                $query->select(DB::raw(1))
                    ->from('E220ITE as ITE')
                    ->whereRaw('ITE.CODDEP = CON.CODDEP')
                    ->whereRaw('ITE.DATINV = CON.DATINV')
                    ->whereRaw('ITE.CODPRO = CON.CODPRO')
                    ->whereRaw('ITE.CODDER = CON.CODDER')
                    ->whereRaw('ITE.CODDEP = CON.CODDEP')
                    ->where('ITE.ULTCON', '=', 0);
            })
            ->get();
    }

    public function getReservaExclusiva($codPro) {
        return DB::table('E210EST')
            ->select('QTDRAE')
            ->where('CODEMP', '=', 1)
            ->where('CODPRO', '=' , $codPro)
            ->where('CODDEP', '=', '219')
            ->get();
    }

    public function addMotAcerto($parans)
    {
        return DB::table('E220CON')
            ->where('CODEMP', '=', $parans['codemp'])
            ->where('DATINV', '=', $parans['datinv'])
            ->where('CODDEP', '=', $parans['coddep'])
            ->where('CODPRO', '=', $parans['codpro'])
            ->where('CODDER', '=', $parans['codder'])
            ->where('NUMCON', '=', $parans['numcon'])
            ->update([
                'MOTACE' => '300'
            ]);
    }
}
