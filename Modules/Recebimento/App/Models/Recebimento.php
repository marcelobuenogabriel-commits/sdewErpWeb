<?php

namespace Modules\Recebimento\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Recebimento\Database\factories\RecebimentoFactory;

class Recebimento extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory()
    {
        //return RecebimentoFactory::new();
    }

    /*
     * Query's de Select
     */

    public function getItensXml($chvNfc) 
    {
        return DB::table('E000IPC')
            ->where('CHVNEL', '=', $chvNfc)
            ->whereNull('USU_USUALT')
            ->get();
    }

    public function getItensrdemCompra($numOcp) 
    {
        return DB::table('E420IPO')
            ->where('CODEMP', '=', 1)
            ->where('CODFIL', '=', 1)
            ->where('NUMOCP', '=', $numOcp)
            ->get();
    }

    public function findNotas($date)
    {
        return DB::table('E000NFC as NFC')
            ->select('FOR.NOMFOR', 'NFC.NUMNFC', DB::raw('SUM(IPC.QTDREC) as QTDREC'), 'NFC.DATEMI')
            ->join('E070FIL as FIL', 'NFC.CGCFIL', '=', 'FIL.NUMCGC')
            ->join('E095FOR as FOR', 'NFC.CGCFOR', '=', 'FOR.CGCCPF')
            ->join('E000IPC as IPC', 'NFC.CHVNEL', '=', 'IPC.CHVNEL')
            ->where('NFC.CGCFIL', '=', '2322789000122')
            ->where('FIL.CODEMP', '=', 1)
            ->where('NFC.CGCFOR', '>', 0)
            ->where('NFC.TIPNFE', '=', 1)
            ->where('NFC.DATEMI', '>=', $date)
            ->where('NFC.INDCAN', '<>', 'S')
            ->where('NFC.CODEDC', '<>', 'NFS')
            ->where('IPC.USU_SITNFC', '=', NULL)
            ->whereRaw("IPC.NOPPRO NOT LIKE '59%%'")
            ->whereRaw("IPC.NOPPRO NOT LIKE '69%%'")
            ->whereRaw('NOT EXISTS (SELECT 1 FROM E440NFC WHERE E440NFC.CHVNEL = NFC.CHVNEL AND E440NFC.CODEMP = FIL.CODEMP AND E440NFC.CODFIL = FIL.CODFIL AND E440NFC.DATENT >= NFC.DATEMI AND E440NFC.SITNFC <> 1)')
            ->groupBy([
                'FOR.NOMFOR',
                'NFC.NUMNFC',
                'NFC.DATEMI'
            ])
            ->orderBy('NFC.DATEMI', 'DESC')
            ->get();
    }

    public function findItens()
    {
        return DB::table('E000NFC as NFC')
            ->join('E000IPC as IPC', 'NFC.CHVNEL', '=', 'IPC.CHVNEL')
            ->join('E095FOR as FOR', 'NFC.CGCFOR', '=', 'FOR.CGCCPF')
            ->join('E420OCP as OCP', function ($join) {
                $join->on('IPC.NUMOCP', '=', 'OCP.NUMOCP');
                $join->on('OCP.CODFOR', '=', 'FOR.CODFOR');
            })
            ->join('E420IPO as IPO', function ($join) {
                $join->on('OCP.NUMOCP', '=', 'IPO.NUMOCP');
                $join->on('OCP.CODEMP', '=', 'IPO.CODEMP');
                $join->on('IPC.SEQIPO', '=', 'IPO.SEQIPO');
            })
            ->join('E075PRO as PRO', function ($join) {
                $join->on('IPO.CODEMP', '=', 'PRO.CODEMP');
                $join->on('IPO.CODPRO', '=', 'PRO.CODPRO');
            })
            ->where('NFC.USU_SITNFC', '=', 1)
            ->whereNull('IPC.USU_USUALT')
            ->get();
    }

    public function getNota()
    {
        return DB::table('E440NFC as NFC')
        ->select([
            'NFC.NUMNFC',
            'NFC.CHVNEL',
            'FORN.APEFOR',
            'FORN.CODFOR',
            DB::raw("CAST(
                100.0 * 
                ISNULL((SELECT SUM(usu_qtdrec) FROM usu_t_pallet WHERE usu_numnfc = NFC.numnfc AND usu_codfor = FORN.CODFOR), 0) /
                NULLIF((SELECT SUM(qtdrec) FROM e440ipc WHERE numnfc = NFC.numnfc AND codfor = FORN.CODFOR), 0)
                AS DECIMAL(5,2)
            ) as PERNFE"),
            DB::raw("STUFF((
                SELECT ', ' + CAST(IPC.NUMOCP AS VARCHAR)
                FROM E440IPC IPC
                WHERE IPC.NUMNFC = NFC.NUMNFC AND IPC.CODFOR = FORN.CODFOR
                GROUP BY IPC.NUMOCP
                FOR XML PATH(''), TYPE
            ).value('.', 'NVARCHAR(MAX)'), 1, 2, '') as NUMOCP"),
        ])
        ->join('E095FOR as FORN', 'FORN.CODFOR', '=', 'NFC.CODFOR')
        ->where('NFC.CODEMP', 1)
        ->where('NFC.CODFIL', 1)
        ->where('NFC.CODSNF', 'NFE')
        ->where('NFC.TIPNFE', 7)
        ->where('NFC.USU_SITNFC', 1)
        ->union(
            DB::table('E000NFC as NFC')
            ->select('NFC.NUMNFC', 'NFC.CHVNEL', 'FORN.APEFOR', 'FORN.CODFOR', 'IPC.USU_TIPNFC','IPC.USU_USUALT', 
                DB::raw("CAST(
                    100.0 * 
                    ISNULL((SELECT SUM(usu_qtdrec) FROM usu_t_pallet WHERE usu_numnfc = NFC.numnfc AND usu_codfor = FORN.CODFOR), 0) /
                    NULLIF((SELECT SUM(qtdrec) FROM e000ipc WHERE numnfc = NFC.numnfc AND codfor = FORN.CODFOR), 0)
                    AS DECIMAL(5,2)
                ) as PERNFE"),
                DB::raw("STUFF((
                    SELECT ', ' + CAST(IPC.NUMOCP AS VARCHAR)
                    FROM E000IPC IPC
                    WHERE IPC.CHVNEL = NFC.CHVNEL
                    GROUP BY IPC.NUMOCP
                    FOR XML PATH(''), TYPE
                ).value('.', 'NVARCHAR(MAX)'), 1, 2, '') as NUMOCP")
            )
            ->join('E000IPC as IPC', 'NFC.CHVNEL', '=', 'IPC.CHVNEL')
            ->join('E095FOR as FORN', 'NFC.CGCFOR', '=', 'FORN.CGCCPF')
            ->where('NFC.USU_SITNFC', '=', 1)
        )
        ->get();
    }

    public function getNotas()
    {
        return DB::table('E000NFC as NFC')
            ->select('NFC.NUMNFC', 'NFC.CHVNEL', 'IPC.USU_TIPNFC', 'IPC.USU_USUALT', 'IPC.SEQIPO', 'FOR.APEFOR', 'FOR.CODFOR', 'OCP.NUMOCP')
            ->join('E000IPC as IPC', 'NFC.CHVNEL', '=', 'IPC.CHVNEL')
            ->join('E095FOR as FOR', 'NFC.CGCFOR', '=', 'FOR.CGCCPF')
            ->leftJoin('E420OCP as OCP', function ($join) {
                $join->on('IPC.NUMOCP', '=', 'OCP.NUMOCP');
                $join->on('FOR.CODFOR', '=', 'OCP.CODFOR');
            })
            ->where('NFC.USU_SITNFC', '=', 1)
            ->union(
                DB::table('E440NFC as NFC')
                    ->select([
                            'NFC.NUMNFC',
                            'NFC.CHVNEL',
                            'IPC.USU_TIPNFC',
                            'IPC.USU_USUALT', 
                            'IPC.SEQIPO', 
                            'FOR.APEFOR', 
                            'FOR.CODFOR', 
                            'IPC.NUMOCP'
                    ])
                    ->join('E440IPC as IPC', function($join){
                        $join->on('IPC.CODEMP', '=', 'NFC.CODEMP');
                        $join->on('IPC.CODFIL', '=', 'NFC.CODFIL');
                        $join->on('IPC.NUMNFC', '=', 'NFC.NUMNFC');
                        $join->on('IPC.CODSNF', '=', 'NFC.CODSNF');
                        $join->on('IPC.CODFOR', '=', 'NFC.CODFOR');
                    })
                    ->join('E095FOR as FOR', function ($join) {
                        $join->on('FOR.CODFOR', '=', 'IPC.CODFOR');
                    })
                    ->where('NFC.CODEMP', '=', 1)
                    ->where('NFC.CODFIL', '=', 1)
                    ->where('NFC.CODSNF', '=', 'NFE')
                    ->where('NFC.TIPNFE', '=', 7)
                    ->where('NFC.USU_SITNFC', '=', 1)
            )
            ->get();
    }

    public function getItensImportacao()
    {
        return DB::table('E440NFC AS NFC')
            ->join('E440IPC as IPC', function($join){
                $join->on('IPC.CODEMP', '=', 'NFC.CODEMP');
                $join->on('IPC.CODFIL', '=', 'NFC.CODFIL');
                $join->on('IPC.NUMNFC', '=', 'NFC.NUMNFC');
                $join->on('IPC.CODSNF', '=', 'NFC.CODSNF');
                $join->on('IPC.CODFOR', '=', 'NFC.CODFOR');
            })
            ->join('E095FOR AS FOR', function ($join) {
                $join->on('FOR.CODFOR', '=', 'IPC.CODFOR');
            })
            ->join('E075PRO AS PRO', function ($join) {
                $join->on('PRO.CODEMP', '=', 'IPC.CODEMP');
                $join->on('PRO.CODPRO', '=', 'IPC.CODPRO');
            })
            ->where('NFC.CODEMP', '=', 1)
            ->where('NFC.CODFIL', '=', 1)
            ->where('NFC.CODSNF', '=', 'NFE')
            ->where('NFC.TIPNFE', '=', 7)
            ->where('NFC.USU_SITNFC', '=', 1)
            ->whereNull('IPC.USU_USUALT')
            ->get();
    }

    public function findNfe($chvNfe)
    {
        return DB::table('E000NFC as NFC')
            ->select('NFC.TIPNFE', 'IPC.NUMOCP', 'IPC.SEQIPO')
            ->join('E000IPC as IPC', 'NFC.CHVNEL', '=', 'IPC.CHVNEL')
            ->where('NFC.CHVNEL', '=', $chvNfe)
            ->get()
            ->first();
    }

    public function findNfeNacional($numNfc, $codFor)
    {
        return DB::table('E440IPC as IPC')
            ->selectRaw('DISTINCT(IPC.NUMOCP) as numocp, IPC.SEQIPO as seqipo')
            ->where('IPC.NUMNFC', '=', $numNfc)
            ->where('IPC.CODFOR', '=', $codFor)
            ->get();
    }

    public function findNfeImportacao($chvNfc)
    {
        return DB::table('E440NFC as NFC')
            ->select('NFC.CODEMP', 'NFC.CODFIL', 'NFC.NUMNFC', 'NFC.TIPNFE', 'NFC.CODFOR', 'IPC.NUMOCP', 'IPC.SEQIPO')
            ->join('E440IPC as IPC', function($join){
                $join->on('IPC.CODEMP', '=', 'NFC.CODEMP');
                $join->on('IPC.CODFIL', '=', 'NFC.CODFIL');
                $join->on('IPC.NUMNFC', '=', 'NFC.NUMNFC');
                $join->on('IPC.CODSNF', '=', 'NFC.CODSNF');
                $join->on('IPC.CODFOR', '=', 'NFC.CODFOR');
            })
            ->where('NFC.CODEMP', '=', 1)
            ->where('NFC.CODFIL', '=', 1)
            ->where('NFC.CODSNF', '=', 'NFE')
            ->where('NFC.TIPNFE', '=', 7)
            ->where('NFC.CHVNEL', '=', $chvNfc)
            ->get()
            ->first();
    }

    public function findOcpByNumOcp($numOcp)
    {
        return DB::table('E420OCP')
            ->where('NUMOCP', '=', $numOcp)
            ->get();
    }

    public function findItemOcp($numOcp, $seqIpo)
    {
        return DB::table('E420IPO')
            ->select('QTDPED')
            ->where('CODEMP', '=', 1)
            ->where('NUMOCP', '=', $numOcp)
            ->where('SEQIPO', '=', $seqIpo)
            ->get();
    }

    public function findItemXML($numNfc, $seqIpo, $chvNel, $numOcp)
    {
        return DB::table('E000IPC')
            ->where('NUMNFC', '=', $numNfc)
            ->where('CHVNEL', '=', $chvNel)
            ->where('SEQIPO', '=', $seqIpo)
            ->where('NUMOCP', '=', $numOcp)
            ->get();
    }

    public function findItemXMLImportacao($numNfc, $seqIpo, $numOcp, $codFor)
    {
        return DB::table('E440IPC')
            ->where('NUMNFC', '=', $numNfc)
            ->where('SEQIPO', '=', $seqIpo)
            ->where('NUMOCP', '=', $numOcp)
            ->where('CODFOR', '=', $codFor)
            ->get();
    }

    public function findPallet($numNfc, $seqIpo, $numOcp)
    {
        return DB::table('USU_T_PALLET')
            ->select(DB::raw('SUM(USU_QTDREC) as QTDREC'))
            ->where('USU_NUMNFC', '=', $numNfc)
            ->where('USU_NUMOCP', '=', $numOcp)
            ->where('USU_SEQIPO', '=', $seqIpo)
            ->groupBy('USU_NUMOCP')
            ->get();
    }
    public function findXml($chvNfc)
    {
        return DB::table('E000IPC')
            ->where('CHVNEL', '=', $chvNfc)
            ->get();
    }

    public function isValidOrdem($numOcp, $codFor)
    {
        return DB::table('E420OCP')
            ->where('NUMOCP', '=', $numOcp)
            ->where('CODFOR', '=', $codFor)
            ->get()
            ->first();
    }

    public function consultPalletNumber($numPrj, $tipPal) 
    {
        return DB::table('USU_TINDPAL')
            ->where('USU_NUMPRJ', '=', $numPrj)
            ->where('USU_TIPPAL', '=', $tipPal)
            ->get()
            ->first();
    }

    /*
     * Query's de Update
     */

    public function removeNfc($chvNfc)
    {
        return DB::table('E000NFC')
            ->where('CHVNEL', '=', $chvNfc)
            ->update([
                'USU_SITNFC' => 2
            ]);
    }

    public function removeNfcImportacao($numNfc, $codFor)
    {
        return DB::table('E440NFC')
            ->where('CODEMP', '=', 1)
            ->where('CODFIL', '=', 1)
            ->where('NUMNFC', '=', $numNfc)
            ->where('CODSNF', '=', 'NFE')
            ->where('CODFOR', '=', $codFor)
            ->update([
                'USU_SITNFC' => 2
            ]);
    }

    public function updateSitApr($chvNfc)
    {
        return DB::table('E000NFC')
            ->where('CHVNEL', '=', $chvNfc)
            ->update([
                'USU_SITNFC' => 1
            ]);
    }

    public function updateSitPrepImportacao($nfe)
    {
        return DB::table('E440NFC')
            ->where('CODEMP', '=', $nfe->CODEMP)
            ->where('CODFIL', '=', $nfe->CODFIL)
            ->where('NUMNFC', '=', $nfe->NUMNFC)
            ->where('CODSNF', '=', 'NFE')
            ->where('CODFOR', '=', $nfe->CODFOR)
            ->update([
                'USU_SITNFC' => 1
            ]);
    }

    public function closeNfcImportacao($numNfc, $codFor)
    {
        return DB::table('E440NFC')
            ->where('CODEMP', '=', 1)
            ->where('CODFIL', '=', 1)
            ->where('NUMNFC', '=', $numNfc)
            ->where('CODSNF', '=', 'NFE')
            ->where('CODFOR', '=', $codFor)
            ->update([
                'USU_SITNFC' => 2
            ]);
    }

    public function closeIpcImportacao($numNfc, $codFor) {
        return DB::table('E440IPC')
            ->where('CODEMP', '=', 1)
            ->where('CODFIL', '=', 1)
            ->where('NUMNFC', '=', $numNfc)
            ->where('CODSNF', '=', 'NFE')
            ->where('CODFOR', '=', $codFor)
            ->update([
                'USU_SITNFC' => 2
            ]);
    }

    public function closeNfc($chvNfc)
    {
        return DB::table('E000NFC')
            ->where('CHVNEL', '=', $chvNfc)
            ->update([
                'USU_SITNFC' => 2
            ]);
    }

    public function closeIpc($chvNfc)
    {
        return DB::table('E000IPC')
            ->where('CHVNEL', '=', $chvNfc)
            ->update([
                'USU_SITNFC' => 2
            ]);
    }

    public function closeIpcXML($numOcp, $seqIpc, $chvNel)
    {
        return DB::table('E000IPC')
            ->where('NUMOCP', '=', $numOcp)
            ->where('SEQIPO', '=', $seqIpc)
            ->where('CHVNEL', '=', $chvNel)
            ->update([
                'USU_USUALT' => 1
            ]);
    }

    public function closeIpcXMLImportacao($numOcp, $seqIpc, $numNfc, $codFor)
    {
        return DB::table('E440IPC')
            ->where('NUMOCP', '=', $numOcp)
            ->where('SEQIPO', '=', $seqIpc)
            ->where('NUMNFC', '=', $numNfc)
            ->where('CODFOR', '=', $codFor)
            ->update([
                'USU_USUALT' => 1
            ]);
    }

    public function updateXmlOc($numOcp, $chvNfc)
    {
        return DB::table('E000IPC')
            ->where('CHVNEL', '=', $chvNfc)
            ->update([
                'NUMOCP' => $numOcp
            ]);
    }

    public function updatePallet($numPrj, $tipPal, $newSeqPallet)
    {
        return DB::table('USU_TINDPAL')
            ->where('USU_NUMPRJ', '=', $numPrj)
            ->where('USU_TIPPAL', '=', $tipPal)
            ->update([
                'USU_INDPAL' => $newSeqPallet
            ]);
    }

    public function updateXmlOcp($chvNfc, $numNfc, $seqIpc, $seqIpo)
    {
        return DB::table('E000IPC')
            ->where('CHVNEL', '=', $chvNfc)
            ->where('NUMNFC', '=', $numNfc)
            ->where('SEQIPC', '=', $seqIpc)
            ->update([
                'SEQIPO' => $seqIpo
            ]);
    }

    /*
    * Query's de Delete
    */
    public function deletePallet($numNfc, $seqIpo, $numOcp, $qtdRec, $codPro)
    {
        return DB::table('USU_T_PALLET')
            ->where('USU_NUMNFC', '=', $numNfc)
            ->where('USU_SEQIPO', '=', $seqIpo)
            ->where('USU_NUMOCP', '=', $numOcp)
            ->where('USU_QTDREC', '=', $qtdRec)
            ->where('USU_CODPRO', '=', $codPro)
            ->delete();
    }

    /*
     * Query's de Insert
     */
    public function insertPallet($date, $numOcp, $seqIpo, $codPal, $codPro, $qtdRec, $numNfc, $codUsu, $codFor)
    {
        return DB::table('USU_T_PALLET')
            ->insert([
                'USU_CODEMP' => '1',
                'USU_CODFIL' => '1',
                'USU_DATREC' => $date,
                'USU_NUMOCP' => $numOcp,
                'USU_SEQIPO' => $seqIpo,
                'USU_ENDPAL' => $codPal,
                'USU_CODPRO' => $codPro,
                'USU_QTDREC' => $qtdRec,
                'USU_CODUSU' => $codUsu,
                'USU_NUMNFC' => $numNfc,
                'USU_CODFOR' => ($codFor <> NULL) ? $codFor : ''
            ]);
    }

    public function createPalle($numPrj, $tipPal)
    {
        return DB::table('USU_TINDPAL')
            ->insert([
                'USU_NUMPRJ' => $numPrj,
                'USU_TIPPAL' => $tipPal,
                'USU_INDPAL' => 1
            ]);
    }
}
