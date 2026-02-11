<?php

namespace Modules\Recebimento\App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Recebimento\Database\factories\PreFaturaFactory;
use Illuminate\Support\Facades\DB;

class PreFatura extends Model
{
    public function getPrefaturas()
    {
        $epdAgg = DB::table('E135EPD as EPD')
            ->select([
                'EPD.codemp',
                'EPD.numane',
                'EPD.numpfa',
                'EPD.SEQPES',
                DB::raw('SUM(EPD.qtdpro) AS qtdpro_total'),
            ])
            ->groupBy([
                'EPD.codemp',
                'EPD.numane',
                'EPD.numpfa',
                'EPD.SEQPES',
            ]);

        return DB::table('E135PFA as PFA')
            ->join('E135PES as PES', function ($join) {
                $join->on('PFA.codemp', '=', 'PES.codemp')
                    ->on('PFA.numane', '=', 'PES.numane')
                    ->on('PFA.numpfa', '=', 'PES.numpfa');
            })
            ->leftJoinSub($epdAgg, 'EPD', function ($join) {
                $join->on('PFA.codemp', '=', 'EPD.codemp')
                    ->on('PFA.numane', '=', 'EPD.numane')
                    ->on('PFA.numpfa', '=', 'EPD.numpfa')
                    ->on('PES.SEQPES', '=', 'EPD.SEQPES');
            })
            ->join('E085CLI as CLI', 'PFA.codcli', '=', 'CLI.codcli')
            ->whereIn('PFA.sitpfa', [2, 3])
            ->select([
                'PFA.codemp',
                'PFA.numane',
                'PFA.numpfa',
                'PFA.codcli',
                'CLI.nomcli',
                'PFA.sitpfa',
            ])
            ->selectRaw('SUM(PES.qtdppf) AS qtd_prefatura_total')
            ->selectRaw('SUM(COALESCE(EPD.qtdpro_total, 0)) AS qtd_embalada_total')
            ->selectRaw('SUM(COALESCE(EPD.qtdpro_total, 0)) - SUM(PES.qtdppf) AS dif_emb_minus_pref')
            ->groupBy([
                'PFA.codemp',
                'PFA.numane',
                'PFA.numpfa',
                'PFA.codcli',
                'CLI.nomcli',
                'PFA.sitpfa',
            ])
            ->havingRaw('SUM(COALESCE(EPD.qtdpro_total, 0)) <> SUM(PES.qtdppf)')
            ->get();
    }

    public function getPrefatura($numane, $numpfa)
    {
        return DB::table('E135PFA')
        ->select([  
                    'E135PES.codemp', 
                    'E135PES.codfil', 
                    'E135PES.numane', 
                    'E135PES.numpfa',
                    'E135PES.codpro', 
                    'E135PES.seqpes',
                    'E135PES.qtdppf',
                    'E210EST.codend',
                    'E135EPD.qtdpro'
        ])
        ->join('E135PES', function($join){
            $join->on('E135PFA.codemp', '=', 'E135PES.codemp')
                ->on('E135PFA.numane', '=', 'E135PES.numane')
                ->on('E135PFA.numpfa', '=', 'E135PES.numpfa');
        })
        ->join('E210EST', function($join){
            $join->on('E135PES.codemp', '=', 'E210EST.codemp');
            $join->on('E135PES.codpro', '=', 'E210EST.codpro');
        })
        ->join('E085CLI', function($join){
            $join->on('E135PFA.codcli', '=', 'E085CLI.codcli');
        })
        ->leftjoin('E135EPD', function($join){
            $join->on('E135EPD.codemp', '=', 'E135EPD.codemp')
                    ->on('E135PES.numane', '=', 'E135EPD.numane')
                    ->on('E135PES.numpfa', '=', 'E135EPD.numpfa')
                    ->on('E135PES.seqpes', '=', 'E135EPD.seqpes');
        })
        ->whereIn('E135PFA.sitpfa', [2,3])
        ->where('E135PFA.numane','=', $numane)
        ->where('E135PFA.numpfa','=', $numpfa)
        ->where('E210EST.coddep', '=', '219')
        ->orderby('E210EST.codend')
        ->get();
    }

    public function getProdutosPrefatura($numane, $numpfa)
    {
        return DB::table('E135PES')
        ->select([  
                    'E135PES.codemp', 
                    'E135PES.codfil', 
                    'E135PES.numane', 
                    'E135PES.numpfa',
                    'E135PES.codpro', 
                    'E135PES.seqpes',
                    DB::raw('SUM(E135EPD.qtdpro) AS qtdpro'),
        ])
        ->join('E135EPD', function($join){
            $join->on('E135EPD.codemp', '=', 'E135EPD.codemp')
                    ->on('E135PES.numane', '=', 'E135EPD.numane')
                    ->on('E135PES.numpfa', '=', 'E135EPD.numpfa')
                    ->on('E135PES.seqpes', '=', 'E135EPD.seqpes');
        })
        ->where('E135PES.numane','=', $numane)
        ->where('E135PES.numpfa','=', $numpfa)
        ->groupby('E135PES.codemp', 'E135PES.codfil', 'E135PES.numane', 'E135PES.numpfa', 'E135PES.codpro', 'E135PES.seqpes')
        ->get();
    }
}
