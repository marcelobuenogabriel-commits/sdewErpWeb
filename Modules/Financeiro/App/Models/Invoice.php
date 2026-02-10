<?php

namespace Modules\Financeiro\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Financeiro\Database\factories\InvoiceFactory;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): InvoiceFactory
    {
        //return InvoiceFactory::new();
    }

    public function getUltimoRegistro()
    {
        return DB::table('USU_TINVOC')->latest('USU_CODINV')->get()->first();
    }

    public function getPedidosImportacao()
    {
        return DB::table('USU_TINVOC as INV')
            ->join('E120PED as PED', function ($join) {
                $join->on('INV.USU_CODEMP', '=', 'PED.CODEMP');
                $join->on('INV.USU_NUMPED', '=', 'PED.NUMPED');
            })
            ->join('E085CLI as CLI', function ($join) {
                $join->on('CLI.CODCLI', '=', 'PED.CODCLI');
            })
            ->where('INV.USU_NUMINV', '=', 3)
            ->get();
    }

    public function findAllPedidos()
    {
        return DB::table('USU_TINVOC as INV')
            ->join('E120PED as PED', function ($join) {
                $join->on('INV.USU_CODEMP', '=', 'PED.CODEMP');
                $join->on('INV.USU_NUMPED', '=', 'PED.NUMPED');
            })
            ->join('E085CLI as CLI', function ($join) {
                $join->on('CLI.CODCLI', '=', 'PED.CODCLI');
            })
            ->where('INV.USU_NUMINV', '=', 2)
            ->get();
    }

    public function findAllContratos()
    {
        return DB::table('USU_TINVOC as INV')
            ->join('E160CTR as CTR', function ($join) {
                $join->on('INV.USU_CODEMP', '=', 'CTR.CODEMP');
                $join->on('INV.USU_NUMPED', '=', 'CTR.NUMCTR');
            })
            ->join('E085CLI as CLI', function ($join) {
                $join->on('CLI.CODCLI', '=', 'CTR.CODCLI');
            })
            ->where('INV.USU_NUMINV', '=', 1)
            ->get();
    }

    public function findAllManagements()
    {
        return DB::table('USU_TINVOC as INV')
            ->where('INV.USU_NUMINV', '=', 3)
            ->get();
    }

    public function findInvoice($codInv)
    {
        return DB::table('USU_TINVOC')
            ->select([
                'usu_codemp as codEmp',
                'usu_codfil as codFil',
                'usu_numord as pedCli',
                'usu_numped as numPed',
                'usu_codinv as codInv',
                'usu_ordby as nomUsu',
                'usu_terpay as datVct',
                'usu_gastot as vlrFat',
                'usu_tipinc as desPro',
                'usu_codinv as codInv',
                'usu_perirf as vlrImp',
                'usu_taxfii as vlrFee',
                'usu_perfat as perFat',
                'usu_codmoe as codMoe',
                'usu_cotmoe as cotMoe',
                'usu_madfor as tipPgt',
                'usu_pesbru as notCre',
                'usu_obsinv as refPed',
                'usu_obspar as obsPar',
                'usu_dimen as datCot',
                'usu_numver as numVer',
                'usu_numinv as tipInv',
                'usu_cmpext as qtdPar',
            ])
            ->where('USU_CODINV', '=', $codInv)
            ->get()
            ->last();
    }

    public function findInvoiceImportacao($codInv)
    {
        return DB::table('USU_TINVOC')
            ->select([
                'usu_codemp as codEmp',
                'usu_codfil as codFil',
                'usu_numped as numPed',
                'usu_endent as endEnt',
                'usu_codinv as codInv',
                'usu_ordby as ordBy',
                'usu_terpay as terPay',
                'usu_gastot as gasTot',
                'usu_gasfre as gasFre',
                'usu_gasseg as gasSeg',
                'usu_gasdin as gasDin',
                'usu_madfor as madFor',
                'usu_pesbru as pesBru',
                'usu_pesnet as pesLiq',
                'usu_numvol as numVol',
                'usu_dimen as dimCax',
                'usu_tipinc as tipInc',
                'usu_numver as numVer',
                'usu_numinv as tipInv',
                'usu_sitinv as sitInv'
            ])
            ->where('USU_CODINV', '=', $codInv)
            ->where('USU_NUMINV', '=', 3)
            ->get()
            ->first();
    }

    public function getProdutosInvoiceImportacao($codInv)
    {
        return DB::table('USU_TINVOC as VOC')
            ->select([
                DB::raw('IPD.QTDABE - (SELECT SUM(USU_QTDPED) FROM USU_TINVITE WHERE usu_numped = VOC.USU_NUMPED AND usu_codite = IPD.codpro) AS QTDABEPED'),
                'INV.USU_CODINV',
                'VOC.USU_NUMPED',
                'PED.NUMPED',
                'IPD.CODPRO',
                'IPD.CPLIPD',
                'IPD.PREUNI',
                'IPD.QTDABE',
                'IPD.SEQIPD',
                'INV.USU_QTDPED',
                'INV.USU_CODITE',
                'INV.USU_CODPAI'
            ])
            ->join('E120PED as PED', function ($join) {
                $join->on('VOC.USU_CODEMP', '=', 'PED.CODEMP');
                $join->on('VOC.USU_NUMPED', '=', 'PED.NUMPED');
            })
            ->join('E120IPD as IPD', function ($join) {
                $join->on('PED.CODEMP', '=', 'IPD.CODEMP');
                $join->on('PED.NUMPED', '=', 'IPD.NUMPED');
            })
            ->leftJoin('USU_TINVITE as INV', function ($join) {
                $join->on('INV.USU_NUMPED', '=', 'PED.NUMPED');
                $join->on('INV.USU_CODITE', '=', 'IPD.CODPRO');
            })
            ->where('VOC.USU_CODINV', '=', $codInv)
            ->where('IPD.SITIPD', '<>', '5') // Cancelado
            ->get();
    }

    public function getVersionInvoicePedido($request)
    {
        return DB::table('USU_TINVOC')
            ->select('USU_NUMVER as numVer')
            ->where('USU_CODEMP', '=', $request->codEmp)
            ->where('USU_NUMPED', '=', $request->codPed)
            ->where('USU_NUMINV', '=', 2)
            ->get()
            ->last();
    }

    public function getVersionInvoiceContrato($request)
    {
        return DB::table('USU_TINVOC')
            ->select('USU_NUMVER as numVer')
            ->where('USU_CODEMP', '=', $request->codEmp)
            ->where('USU_NUMPED', '=', $request->numCtr)
            ->where('USU_NUMINV', '=', 1)
            ->get()
            ->last();
    }

    public function getUltimoRegistroProdutos()
    {
        return DB::table('USU_TINVITE')->latest('USU_NUMITE')->get()->first();
    }

    public function getUltimaVersaoInvoiceImportacao($codEmp, $numPed)
    {
        return DB::table('USU_TINVOC')
            ->select('USU_NUMVER as versao')
            ->where('USU_CODEMP', '=', $codEmp)
            ->where('USU_NUMPED', '=', $numPed)
            ->where('USU_NUMINV', '=', 3)
            ->get()
            ->last();
    }

    public function getEnderecoInvoiceImportacao($codEmp, $codInv)
    {
        return DB::table('USU_TINVEND')
            ->select([
                'usu_endcli as endCli',
                'usu_concli as conCli',
                'usu_cidcli as cidCli',
                'usu_paicli as paiCli',
                'usu_enddes as endDes',
                'usu_condes as conDes',
                'usu_ciddes as cidDes',
                'usu_paides as paiDes',
            ])
            ->where('USU_CODEMP', '=', $codEmp)
            ->where('USU_CODINV', '=', $codInv)
            ->get()
            ->first();
    }


    /*
    *   UPDATE query 
    */
    public function updateInvoiceImportacao($codInv, $data)
    {
        return DB::table('USU_TINVOC')
            ->where('USU_CODINV', '=', $codInv)
            ->where('USU_NUMINV', '=', 3)
            ->update([
                'usu_codemp' => $data['codEmp'],
                'usu_numped' => (int) $data['codPed'],
                'usu_ordby'  => $data['ordBy'],
                'usu_madfor' => $data['madFor'],
                'usu_pesbru' => (float) $data['pesBru'],
                'usu_pesnet' => (float) $data['pesLiq'],
                'usu_numvol' => (int) $data['numVol'],
                'usu_dimen'  => $data['dimCax'],
                'usu_gastot' => (float) str_replace(",", ".", $data['gasTot']),
                'usu_gasfre' => (float) str_replace(",", ".", $data['gasFre']),
                'usu_gasseg' => (float) str_replace(",", ".", $data['gasSeg']),
                'usu_gasdin' => (float) str_replace(",", ".", $data['gasSeg']),
                'usu_tipinc' => $data['tipInc'],
                'usu_sitinv' => (int) $data['sitInv'],
            ]);
    }

    public function editInvoicePedido($request, $id, $codUsu, $invoice)
    {
        return DB::table('USU_TINVOC')
            ->where('USU_CODINV', '=', $id)
            ->where('USU_NUMVER', '=', $invoice->numVer)
            ->where('USU_NUMINV', '=', 2)
            ->update([
                'usu_codemp' => $request->codEmp,
                'usu_numped' => $request->codPed,
                'usu_numord' => $request->pedCli,
                'usu_ordby' => $request->nomUsu,
                'usu_perirf' => $request->vlrImp,
                'usu_taxfii' => $request->vlrFee,
                'usu_perfat' => $request->perFat,
                'usu_gastot' => $request->vlrFat,
                'usu_codmoe' => $request->codMoe,
                'usu_cotmoe' => $request->cotMoe,
                'usu_terpay' => $request->datVct,
                'usu_madfor' => $request->tipPgt,
                'usu_pesbru' => $request->notCre,
                'usu_tipinc' => $request->desPro,
                'usu_obsinv' => $request->refPed,
                'usu_obspar' => $request->obsPar,
                'usu_cretby' => $codUsu
            ]);
    }

    public function editInvoiceContrato($request, $id, $codUsu, $invoice)
    {
        return DB::table('USU_TINVOC')
            ->where('USU_CODINV', '=', $id)
            ->where('USU_NUMVER', '=', $invoice->numVer)
            ->where('USU_NUMINV', '=', 1)
            ->update([
                'usu_codemp' => $request->codEmp,
                'usu_numped' => $request->numCtr,
                'usu_numord' => $request->pedCli,
                'usu_ordby' => $request->nomUsu,
                'usu_perirf' => $request->vlrImp,
                'usu_taxfii' => $request->vlrFee,
                'usu_cmpext' => $request->qtdPar,
                'usu_gastot' => $request->vlrFat,
                'usu_codmoe' => $request->codMoe,
                'usu_cotmoe' => $request->cotMoe,
                'usu_dimen' => $request->datCot,
                'usu_terpay' => $request->datVct,
                'usu_madfor' => $request->tipPgt,
                'usu_pesbru' => $request->notCre,
                'usu_tipinc' => $request->desPro,
                'usu_obsinv' => $request->refPed,
                'usu_obsinv' => $request->obsPar,
                'usu_cretby' => $codUsu
            ]);
    }

    public function updateEnderecoInvoiceImportacao($codInv, $endEnt)
    {
        return DB::table('USU_TINVEND')
            ->where('USU_CODINV', '=', $codInv)
            ->update([
                'usu_endcli' => $endEnt['endCli'],
                'usu_concli' => $endEnt['conCli'],
                'usu_cidcli' => $endEnt['cidCli'],
                'usu_paicli' => $endEnt['paiCli'],
                'usu_enddes' => $endEnt['endDes'],
                'usu_condes' => $endEnt['conDes'],
                'usu_ciddes' => $endEnt['cidDes'],
                'usu_paides' => $endEnt['paiDes'],
            ]);
    }

    /*
    *   INSERT query
    */
    public function addInvoicePedido($data, $invoice, $version, $codUsu)
    {
        return DB::table('USU_TINVOC')
            ->insert([
                'usu_codemp' => $data->codEmp,
                'usu_codfil' => 1,
                'usu_numped' => $data->codPed,
                'usu_codinv' => $invoice,
                'usu_ordby' => $data->nomUsu,
                'usu_datfec' => date("Y-m-d"),
                'usu_terpay' => $data->datVct,
                'usu_madfor' => $data->tipPgt,
                'usu_pesbru' => $data->notCre, //Adiciona se a Invoice é uma nota de crédito
                'usu_perirf' => $data->vlrImp,
                'usu_gastot' => (float)$data->vlrFat,
                'usu_tipinc' => $data->desPro,
                'usu_numver' => (int)$version,
                'usu_numinv' => 2, //Origem da Invoice - Pedido
                'usu_taxfii' => (float)$data->vlrFee,
                'usu_codmoe' => $data->codMoe,
                'usu_cotmoe' => (float)$data->cotMoe,
                'usu_obsinv' => $data->refPed,
                'usu_cretby' => $codUsu,
                'usu_numord' => $data->pedCli,
                'usu_perfat' => (float)$data->perFat,
                'usu_obspar' => $data->obsPar
            ]);
    }

    public function addInvoiceContrato($data, $invoice, $version, $codUsu)
    {

        return DB::table('USU_TINVOC')
            ->insert([
                'usu_codemp' => $data->codEmp,
                'usu_codfil' => 1,
                'usu_numped' => $data->numCtr,
                'usu_codinv' => $invoice,
                'usu_ordby' => $data->nomUsu,
                'usu_datfec' => date("Y-m-d"),
                'usu_terpay' => $data->datVct,
                'usu_perirf' => $data->vlrImp,
                'usu_madfor' => $data->tipPgt,
                'usu_pesbru' => $data->notCre, //Adiciona se a Invoice é uma nota de crédito
                'usu_tipinc' => $data->desPro,
                'usu_numver' => (int)$version,
                'usu_numinv' => 1, //Origem da Invoice - Contrato
                'usu_taxfii' => (float)$data->vlrFee,
                'usu_codmoe' => $data->codMoe,
                'usu_cotmoe' => (float)$data->cotMoe,
                'usu_dimen'  => $data->datCot,
                'usu_cmpext' => $data->qtdPar,
                'usu_cretby' => $codUsu,
                'usu_numord' => $data->pedCli,
                'usu_obsinv' => $data->obsPar
            ]);
    }

    public function addInvoiceImportacao($data)
    {
        return DB::table('USU_TINVOC')
            ->insert([
                'usu_codemp' => $data['codEmp'],
                'usu_codfil' => 1,
                'usu_numped' => (int) $data['codPed'],
                'usu_codinv' => $data['codInv'],
                'usu_ordby'  => $data['ordBy'],
                'usu_datfec' => date("Y-m-d"),
                'usu_madfor' => $data['madFor'],
                'usu_pesbru' => (float) $data['pesBru'],
                'usu_pesnet' => (float) $data['pesLiq'],
                'usu_numvol' => (int) $data['numVol'],
                'usu_dimen'  => $data['dimCax'],
                'usu_gastot' => (float) str_replace(",", ".", $data['gasTot']),
                'usu_gasfre' => (float) str_replace(",", ".", $data['gasFre']),
                'usu_gasseg' => (float) str_replace(",", ".", $data['gasSeg']),
                'usu_gasdin' => (float) str_replace(",", ".", $data['gasSeg']),
                'usu_tipinc' => $data['tipInc'],
                'usu_numver' => (int) $data['versao'],
                'usu_numinv' => (int) 3, //Origem da Invoice - Importação
                'usu_sitinv' => (int) $data['sitInv'],
            ]);
    }

    public function addEnderecoInvoiceImportacao($codEmp, $codInv, $data)
    {
        return DB::table('USU_TINVEND')
            ->insert([
                'usu_codemp' => $codEmp,
                'usu_codinv' => $codInv,
                'usu_endcli' => $data['endCli'],
                'usu_concli' => $data['conCli'],
                'usu_cidcli' => $data['cidCli'],
                'usu_paicli' => $data['paiCli'],
                'usu_enddes' => $data['endDes'],
                'usu_condes' => $data['conDes'],
                'usu_ciddes' => $data['cidDes'],
                'usu_paides' => $data['paiDes'],
            ]);
    }

    public function addProdutosInvoiceImportacao($codEmp, $numPed, $codInv, $produtoId, $quantidade, $ultimoRegistro, $paisOrigem)
    {
        return DB::statement('
            INSERT INTO USU_TINVITE (
                USU_NUMITE, 
                USU_NUMPED, 
                USU_CODITE, 
                USU_DESITE, 
                USU_QTDPED, 
                USU_VLRLIQ, 
                USU_NUMNCM, 
                USU_CODINV, 
                USU_NUMVER,
                USU_CODPAI
            )
            SELECT 
                ?,                -- USU_NUMITE (do parâmetro $produtoId)
                ?,                -- USU_NUMPED (do parâmetro $numPed)
                ipd.codpro,       -- USU_CODITE (do select)
                ipd.cplipd,       -- USU_DESITE (do select)
                ?,                -- USU_QTDPED (do parâmetro $quantidade)
                (? * ipd.preuni), -- USU_VLRLIQ (do select)
                NULL,             -- USU_NUMNCM (ajuste se necessário)
                ?,                -- USU_CODINV (do parâmetro $codInv)
                1,                 -- USU_NUMVER (fixo ou variável, ajuste se necessário)
                ?                 -- USU_CODPAI (do parâmetro $paisOrigem)
            FROM e120ipd ipd
            WHERE ipd.codemp = ? and ipd.numped = ? and ipd.seqipd = ?
        ', [
            $ultimoRegistro,   // Chave primária única para da tabela USU_TINVITE
            $numPed,           // Número do pedido do ERP 
            $quantidade,       // Quantidade de Produto selecionado na criação da Invoice
            $quantidade,       // Quantidade de Produto selecionado na criação da Invoice (para cálculo do valor total)
            $codInv,           // Número da Invoice 
            $paisOrigem,       // País de Origem
            $codEmp,           // Código da Empresa
            $numPed,           // Número do Pedido
            $produtoId       // ID do Produto
        ]);
    }

    /*
    *   QUERY DELETE
    */
    public function deleteProdutoInvoiceImportacao($codInv, $produtoId)
    {
        return DB::table('USU_TINVITE')
            ->where('USU_CODINV', '=', $codInv)
            ->where('USU_CODITE', '=', $produtoId)
            ->delete();
    }
    
    public function setValorAttribute($value)
    {
        // Remove tudo que não for número, vírgula ou ponto
        $clean = preg_replace('/[^\d,\.]/', '', $value);

        // Troca vírgula por ponto
        $clean = str_replace(',', '.', $clean);

        // Converte para float e salva no atributo
        return floatval($clean);
    }

}
