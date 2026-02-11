<?php

namespace Modules\Lista\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Lista\Database\factories\ListaFactory;

class Lista extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): ListaFactory
    {
        //return ListaFactory::new();
    }

    public function findAll()
    {
        return DB::table('USU_TSUPDGR as DGR')
            ->select('DGR.USU_SEQPRO', 'DGR.USU_DEPORI', 'DGR.USU_DEPDES', 'DGR.USU_NUMPRJ', 'DGR.USU_CODFPJ')
            ->join('USU_TSUPITE as ITE', function ($join) {
                $join->on('DGR.USU_SEQPRO', '=', 'ITE.USU_SEQPRO');
                $join->on('DGR.USU_CODEMP', '=', 'ITE.USU_CODEMP');
            })
            ->whereNull('DGR.USU_SITLIS')
            ->where('ITE.USU_INDTRF', '=', 'S')
            ->groupBy(['DGR.USU_SEQPRO', 'DGR.USU_DEPORI', 'DGR.USU_DEPDES', 'DGR.USU_NUMPRJ', 'DGR.USU_CODFPJ'])
            ->get();
    }

    public function checkLista($seqPro)
    {
        return DB::table('USU_TSUPITE as ITE')
            ->join('USU_TSUPDGR as DGR', function ($join) {
                $join->on('ITE.USU_CODEMP', '=', 'DGR.USU_CODEMP');
                $join->on('ITE.USU_SEQPRO', '=', 'DGR.USU_SEQPRO');
            })
            ->join('E210EST as EST', function ($join) {
                $join->on('ITE.USU_CODPRO', '=', 'EST.CODPRO');
                $join->on('DGR.USU_DEPORI', '=', 'EST.CODDEP');
            })
            ->join('E210MVP as MVP', function ($join) {
                $join->on('MVP.CODEMP', '=', 'ITE.USU_CODEMP');
                $join->on('MVP.CODPRO', '=', 'ITE.USU_CODPRO');
                $join->on('MVP.CODDER', '=', 'ITE.USU_CODDER');
                $join->on('MVP.CODDEP', '=', 'ITE.USU_CODDEP');
                $join->on('MVP.DATMOV', '=', 'ITE.USU_DATMOV');
                $join->on('MVP.SEQMOV', '=', 'ITE.USU_SEQMOV');
                $join->on('MVP.CODEMP', '=', 'EST.CODEMP');
                $join->on('MVP.CODPRO', '=', 'EST.CODPRO');
                $join->on('MVP.CODDER', '=', 'EST.CODDER');
                $join->on('MVP.CODDEP', '=', 'EST.CODDEP');
            })
            ->where('DGR.USU_SEQPRO', '=', $seqPro)
            ->whereRaw('(ITE.USU_SITITE = 0 OR ITE.USU_SITITE IS NULL)')
            ->where('ITE.USU_NUMLIV', '=', 999)
            ->groupBy(['ITE.USU_CODPRO', 'ITE.USU_DESPRO', 'ITE.USU_CODEMP', 'ITE.USU_SEQPRO', 'ITE.USU_CODDER',
                'ITE.USU_SEQMOV', 'ITE.USU_QTDMOV', 'ITE.USU_DATMOV', 'ITE.USU_NUMLIV', 'ITE.USU_NUMIND',
                'MVP.CODEMP', 'mvp.CODPRO', 'mvp.CODDER', 'mvp.CODDEP', 'mvp.DATMOV', 'MVP.SEQMOV',
                'EST.CODEMP', 'EST.CODPRO', 'EST.CODDER', 'EST.CODDEP', 'EST.CODEND',
                'DGR.USU_CODEMP', 'DGR.USU_SEQPRO', 'DGR.USU_CODCCU', 'DGR.USU_NUMPRJ', 'DGR.USU_CODFPJ',
                'DGR.USU_DEPORI', 'DGR.USU_DEPDES'])
            ->orderBy('ITE.USU_NUMLIV')
            ->orderBy('EST.CODEND')
            ->count();
    }

    public function getIntesLista($seqPro)
    {
        return DB::table('USU_TSUPITE as ITE')
            ->select(['ITE.USU_CODPRO', 'ITE.USU_DESPRO', 'ITE.USU_CODEMP', 'ITE.USU_SEQPRO', 'ITE.USU_CODDER',
                'ITE.USU_SEQMOV', 'ITE.USU_QTDMOV', 'ITE.USU_DATMOV', 'ITE.USU_NUMLIV', 'ITE.USU_NUMIND',
                'MVP.CODEMP', 'mvp.CODPRO', 'mvp.CODDER', 'mvp.CODDEP', 'mvp.DATMOV', 'MVP.SEQMOV',
                'EST.CODEMP', 'EST.CODPRO', 'EST.CODDER', 'EST.CODDEP', 'EST.CODEND',
                'DGR.USU_CODEMP', 'DGR.USU_SEQPRO', 'DGR.USU_CODCCU', 'DGR.USU_NUMPRJ', 'DGR.USU_CODFPJ',
                'DGR.USU_DEPORI', 'DGR.USU_DEPDES'])
            ->join('USU_TSUPDGR as DGR', function ($join) {
                $join->on('ITE.USU_CODEMP', '=', 'DGR.USU_CODEMP');
                $join->on('ITE.USU_SEQPRO', '=', 'DGR.USU_SEQPRO');
            })
            ->join('E210EST as EST', function ($join) {
                $join->on('ITE.USU_CODPRO', '=', 'EST.CODPRO');
                $join->on('DGR.USU_DEPORI', '=', 'EST.CODDEP');
            })
            ->join('E210MVP as MVP', function ($join) {
                $join->on('MVP.CODEMP', '=', 'ITE.USU_CODEMP');
                $join->on('MVP.CODPRO', '=', 'ITE.USU_CODPRO');
                $join->on('MVP.CODDER', '=', 'ITE.USU_CODDER');
                $join->on('MVP.CODDEP', '=', 'ITE.USU_CODDEP');
                $join->on('MVP.DATMOV', '=', 'ITE.USU_DATMOV');
                $join->on('MVP.SEQMOV', '=', 'ITE.USU_SEQMOV');
                $join->on('MVP.CODEMP', '=', 'EST.CODEMP');
                $join->on('MVP.CODPRO', '=', 'EST.CODPRO');
                $join->on('MVP.CODDER', '=', 'EST.CODDER');
                $join->on('MVP.CODDEP', '=', 'EST.CODDEP');
            })
            ->where('DGR.USU_SEQPRO', '=', $seqPro)
            ->whereRaw('(ITE.USU_SITITE = 0 OR ITE.USU_SITITE IS NULL)')
            ->groupBy(['ITE.USU_CODPRO', 'ITE.USU_DESPRO', 'ITE.USU_CODEMP', 'ITE.USU_SEQPRO', 'ITE.USU_CODDER',
                'ITE.USU_SEQMOV', 'ITE.USU_QTDMOV', 'ITE.USU_DATMOV', 'ITE.USU_NUMLIV', 'ITE.USU_NUMIND',
                'MVP.CODEMP', 'mvp.CODPRO', 'mvp.CODDER', 'mvp.CODDEP', 'mvp.DATMOV', 'MVP.SEQMOV',
                'EST.CODEMP', 'EST.CODPRO', 'EST.CODDER', 'EST.CODDEP', 'EST.CODEND',
                'DGR.USU_CODEMP', 'DGR.USU_SEQPRO', 'DGR.USU_CODCCU', 'DGR.USU_NUMPRJ', 'DGR.USU_CODFPJ',
                'DGR.USU_DEPORI', 'DGR.USU_DEPDES'])
            ->orderBy('ITE.USU_NUMLIV')
            ->orderBy('EST.CODEND')
            ->get();
    }

    public function getIntesListaLivro($seqPro, $numLiv)
    {
        return DB::table('USU_TSUPITE as ITE')
            ->select(['ITE.USU_CODPRO', 'ITE.USU_DESPRO', 'ITE.USU_CODEMP', 'ITE.USU_SEQPRO', 'ITE.USU_CODDER',
                'ITE.USU_SEQMOV', 'ITE.USU_QTDMOV', 'ITE.USU_DATMOV', 'ITE.USU_NUMLIV', 'ITE.USU_NUMIND',
                'MVP.CODEMP', 'mvp.CODPRO', 'mvp.CODDER', 'mvp.CODDEP', 'mvp.DATMOV', 'MVP.SEQMOV',
                'EST.CODEMP', 'EST.CODPRO', 'EST.CODDER', 'EST.CODDEP', 'EST.CODEND',
                'DGR.USU_CODEMP', 'DGR.USU_SEQPRO', 'DGR.USU_CODCCU', 'DGR.USU_NUMPRJ', 'DGR.USU_CODFPJ',
                'DGR.USU_DEPORI', 'DGR.USU_DEPDES'])
            ->join('USU_TSUPDGR as DGR', function ($join) {
                $join->on('ITE.USU_CODEMP', '=', 'DGR.USU_CODEMP');
                $join->on('ITE.USU_SEQPRO', '=', 'DGR.USU_SEQPRO');
            })
            ->join('E210EST as EST', function ($join) {
                $join->on('ITE.USU_CODPRO', '=', 'EST.CODPRO');
                $join->on('DGR.USU_DEPORI', '=', 'EST.CODDEP');
            })
            ->join('E210MVP as MVP', function ($join) {
                $join->on('MVP.CODEMP', '=', 'ITE.USU_CODEMP');
                $join->on('MVP.CODPRO', '=', 'ITE.USU_CODPRO');
                $join->on('MVP.CODDER', '=', 'ITE.USU_CODDER');
                $join->on('MVP.CODDEP', '=', 'ITE.USU_CODDEP');
                $join->on('MVP.DATMOV', '=', 'ITE.USU_DATMOV');
                $join->on('MVP.SEQMOV', '=', 'ITE.USU_SEQMOV');
                $join->on('MVP.CODEMP', '=', 'EST.CODEMP');
                $join->on('MVP.CODPRO', '=', 'EST.CODPRO');
                $join->on('MVP.CODDER', '=', 'EST.CODDER');
                $join->on('MVP.CODDEP', '=', 'EST.CODDEP');
            })
            ->where('DGR.USU_SEQPRO', '=', $seqPro)
            ->where('ITE.USU_NUMLIV', '=', $numLiv)
            ->whereRaw('(ITE.USU_SITITE = 0 OR ITE.USU_SITITE IS NULL)')
            ->groupBy(['ITE.USU_CODPRO', 'ITE.USU_DESPRO', 'ITE.USU_CODEMP', 'ITE.USU_SEQPRO', 'ITE.USU_CODDER',
                'ITE.USU_SEQMOV', 'ITE.USU_QTDMOV', 'ITE.USU_DATMOV', 'ITE.USU_NUMLIV', 'ITE.USU_NUMIND',
                'MVP.CODEMP', 'mvp.CODPRO', 'mvp.CODDER', 'mvp.CODDEP', 'mvp.DATMOV', 'MVP.SEQMOV',
                'EST.CODEMP', 'EST.CODPRO', 'EST.CODDER', 'EST.CODDEP', 'EST.CODEND',
                'DGR.USU_CODEMP', 'DGR.USU_SEQPRO', 'DGR.USU_CODCCU', 'DGR.USU_NUMPRJ', 'DGR.USU_CODFPJ',
                'DGR.USU_DEPORI', 'DGR.USU_DEPDES'])
            ->orderBy('ITE.USU_NUMLIV')
            ->orderBy('EST.CODEND')
            ->get();
    }

    public function consistSeqProc($seqPro)
    {
        return DB::table('USU_TSUPDGR')
            ->where('USU_SEQPRO', '=', $seqPro)
            ->get()->last();
    }

    public function consistProd($seqPro, $codPro)
    {
        return DB::table('USU_TSUPITE')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPRO', '=', $seqPro)
            ->where('USU_CODPRO', '=', $codPro)
            ->where('USU_INDTRF', '=', 'S')
            ->where('USU_SITITE', '<>', '1')
            ->get()->last();
    }

    public function consistTrecProc($seqPro, $codPro)
    {
        return DB::table('USU_TRECPROC')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPROC', '=', $seqPro)
            ->where('USU_CODPRO', '=', $codPro)
            ->where('USU_SITITE', '<>', 4)
            ->get();
    }

    public function getDispEstoque($codPro, $codDer, $depOri, $qtdMov)
    {
        return DB::table('E210EST')
            ->where('CODEMP', '=', 1)
            ->where('CODPRO', '=', $codPro)
            ->where('CODDER', '=', $codDer)
            ->where('CODDEP', '=', $depOri)
            ->whereRaw('(QTDEST - QTDRES >= ' . $qtdMov . ')')
            ->get()->last();
    }

    public function getLigDeposito($codPro, $codDer, $depDes)
    {
        return DB::table('E210EST')
            ->where('CODEMP', '=', 1)
            ->where('CODPRO', '=', $codPro)
            ->where('CODDER', '=', $codDer)
            ->where('CODDEP', '=', $depDes)
            ->get();
    }

    public function getUnidadeMedida($codPro)
    {
        return DB::table('E075PRO')
            ->where('CODEMP', '=', 1)
            ->where('CODPRO', '=', $codPro)
            ->get();
    }

    public function consistTrecProcLista($seqPro)
    {
        return DB::table('USU_TRECPROC')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SITITE', '<>', '4')
            ->where('USU_SEQPROC', '=', $seqPro)
            ->get();
    }

    public function consistTrecProcQtd($seqPro)
    {
        return DB::table('USU_TRECPROC')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SITITE', '<>', '4')
            ->whereRaw('USU_QTDORI > USU_QTDMOV')
            ->where('USU_SEQPROC', '=', $seqPro)
            ->get();
    }

    public function consistTrecProcFinal($seqPro)
    {
        return DB::table('USU_TRECPROC')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPROC', '=', $seqPro)
            ->whereNull('USU_SITITE')
            ->get();
    }

    public function getLastRegistDGR()
    {
        return DB::table('USU_TESTDGR')->max('USU_SEQPRO');
    }

    public function getContaFinanceira($codPro)
    {
        return DB::table('USU_TRECPROC as PROC')
            ->join('E075PRO as PRO', function ($join) {
                $join->on('PROC.USU_CODPRO', '=', 'PRO.CODPRO');
            })
            ->join('E012FAM as FAM', function ($join) {
                $join->on('PRO.CODFAM', '=', 'FAM.CODFAM');
            })
            ->where('PROC.USU_CODPRO', '=', $codPro)
            ->get();
    }

    public function getQuantidadeProd($codPro, $depOri)
    {
        return DB::table('E210EST')
            ->where('CODPRO', '=', $codPro)
            ->where('CODDEP', '=', $depOri)
            ->get()->last();
    }

    /*
     * Query's de UPDATE
     */
    public function updateTsupIte($seqPro, $codPro, $numInd, $numLvr)
    {
        return DB::table('USU_TSUPITE')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPRO', '=', $seqPro)
            ->where('USU_NUMIND', '=', $numInd)
            ->where('USU_CODPRO', '=', $codPro)
            ->update([
                'USU_NUMLIV' => $numLvr
            ]);
    }

    public function updateTrecProc($seqPro, $codPro, $now)
    {
        return DB::table('USU_TRECPROC')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPROC', '=', $seqPro)
            ->where('USU_CODPRO', '=', $codPro)
            ->update([
                'USU_DATMOV' => $now
            ]);
    }

    public function setMovZerado($seqPro, $codPro)
    {
        return DB::table('USU_TRECPROC')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPROC', '=', $seqPro)
            ->where('USU_CODPRO', '=', $codPro)
            ->update([
                'USU_QTDMOV' => 0,
                'USU_SITTE' => 0
            ]);
    }

    public function updateTrecProcInfo($qtdMov, $numPro, $depDes, $seqPro, $codPro, $codPlt)
    {
        return DB::table('USU_TRECPROC')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPROC', '=', $seqPro)
            ->where('USU_CODPRO', '=', $codPro)
            ->update([
                'USU_CODEND' => $codPlt,
                'USU_QTDMOV' => $qtdMov,
                'USU_SITITE' => 0,
                'USU_CAMPO1' => $numPro,
                'USU_CAMPO2' => $depDes
            ]);
    }

    public function closeItensLista($seqPro, $codPro, $codUsu)
    {
        return DB::table('USU_TSUPITE')
            ->where('USU_CODEMP', '=', '1')
            ->where('USU_SEQPRO', '=', $seqPro)
            ->where('USU_CODPRO', '=', $codPro)
            ->update([
                'USU_SITITE' => 1,
                'USU_USUFEC' => $codUsu
            ]);
    }

    public function rollbackTrecProc($seqPro, $codPro)
    {
        return DB::table('USU_TSUPITE')
            ->where('USU_CODEMP', '=', '1')
            ->where('USU_SEQPRO', '=', $seqPro)
            ->where('USU_CODPRO', '=', $codPro)
            ->update([
                'USU_SITITE' => NULL,
                'USU_USUFEC' => NULL
            ]);
    }

    public function updateTsupDgr($seqPro)
    {
        return DB::table('USU_TSUPDGR')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPRO', '=', $seqPro)
            ->update([
                'USU_SITLIS' => 1
            ]);
    }

    public function updateTrecProcZero($seqPro)
    {
        return DB::table('USU_TRECPROC')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPROC', '=', $seqPro)
            ->whereNull('USU_SITITE')
            ->update([
                'USU_SITITE' => 0
            ]);
    }

    public function finalizaTrecProc($seqPro)
    {
        return DB::table('USU_TRECPROC')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPROC', '=', $seqPro)
            ->update([
                'USU_SITITE' => 4
            ]);
    }

    public function cancelLista($seqPro)
    {
        return DB::table('USU_TSUPDGR')
            ->where('USU_CODEMP', '=', 1)
            ->where('USU_SEQPRO', '=', $seqPro)
            ->update([
                'USU_SITLIS' => 5
            ]);
    }

    /*
     * Query's de INSERT
     */
    public function insertTrecProc($seqPro, $codPro, $now)
    {
        $select = DB::table('USU_TSUPITE as ITE')
            ->select('ITE.USU_CODEMP', 'ITE.USU_SEQPRO', 'ITE.USU_CODPRO',
                'DGR.USU_DEPORI', 'ITE.USU_QTDMOV', 'DGR.USU_NUMPRJ', 'DGR.USU_DEPDES')
            ->join('USU_TSUPDGR as DGR', function ($join) {
                $join->on('ITE.USU_CODEMP', '=', 'DGR.USU_CODEMP');
                $join->on('ITE.USU_SEQPRO', '=', 'DGR.USU_SEQPRO');
            })
            ->whereRaw('EXISTS (SELECT 1 FROM USU_TRECPROC A
                                WHERE A.USU_CodEmp = ITE.USU_CodEmp AND
                                      A.USU_SeqProc = ITE.USU_SeqPro AND
                                      A.USU_CodPro = ITE.USU_CodPro)')
            ->where('ITE.USU_INDTRF', '=', 'S')
            ->where('DGR.USU_CODEMP', '=', 1)
            ->where('DGR.USU_SEQPRO', '=', $seqPro)
            ->where('ITE.USU_CODPRO', '=', $codPro)
            ->get()->last();

        return DB::table('USU_TRECPROC')
            ->insert([
                'USU_CODEMP' => $select->USU_CODEMP,
                'USU_SEQPROC' => $select->USU_SEQPRO,
                'USU_CODPRO' => $select->USU_CODPRO,
                'USU_CODDEP' => $select->USU_DEPORI,
                'USU_DATMOV' => $now,
                'USU_QTDORI' => $select->USU_QTDMOV,
                'USU_CAMPO1' => $select->USU_NUMPRJ,
                'USU_CAMPO2' => $select->USU_DEPDES
            ]);
    }

    public function insertLigProduto($codPro, $codDer, $depDes, $uniMed, $now, $codUsu)
    {
        return DB::table('E210EST')
            ->insert([
                'CODEMP' => 1,
                'CODPRO' => $codPro,
                'CODDER' => $codDer,
                'CODDEP' => $depDes,
                'DATINI' => $now,
                'SALINI' => 0,
                'NIVDEP' => 0,
                'UNIMED' => $uniMed,
                'ESTNEG' => 'N',
                'QTDEST' => 0,
                'QTDBLO' => 0,
                'QTDRES' => 0,
                'QTDRAE' => 0,
                'QTDORD' => 0,
                'QTDCCL' => 0,
                'QTDCFO' => 0,
                'ESTREP' => 0,
                'ESTMIN' => 0,
                'ESTMAX' => 0,
                'ESTMID' => 0,
                'ESTMAD' => 0,
                'DATCCR' => 0,
                'QTDCCR' => 0,
                'DATUEN' => 0,
                'DATUSA' => 0,
                'DATVAL' => 0,
                'INDINV' => 0,
                'SITEST' => 'A',
                'CODMOT' => 0,
                'USUGER' => $codUsu,
                'DATGER' => $now,
                'HORGER' => 900,
                'PRZRSU' => 0,
                'QTDEMB' => 0,
                'ESTCAP' => 0,
                'DATUAN' => 0,
                'LIGESP' => 'N'
            ]);
    }

    public function addNewHeaderDG($seqPro, $codUsu, $now, $horSis)
    {
        return DB::table('USU_TESTDGR')
            ->insert([
                'USU_CODEMP' => 1,
                'USU_SEQPRO' => $seqPro,
                'USU_CODCCU' => '32130',
                'USU_NUMPRJ' => 1,
                'USU_CODFPJ' => 1,
                'USU_CTAFIN' => 1480,
                'USU_DEPORI' => '2617',
                'USU_DEPDES' => NULL,
                'USU_USUGER' => $codUsu,
                'USU_DATGER' => $now,
                'USU_HORGER' => $horSis,
                'USU_INDREQ' => 'N',
                'USU_INDTRF' => 'N'
            ]);
    }

    public function addNewItemDG($new_regist, $codPro, $qtdEst, $qtdNut, $ctaFin, $codUsu, $now, $horSis)
    {
        return DB::table('USU_TESTITE')
            ->insert([
                'USU_CODEMP' => 1,
                'USU_SEQPRO' => $new_regist,
                'USU_CODPRO' => $codPro,
                'USU_CODDER' => '',
                'USU_QTDEST' => $qtdEst,
                'USU_QTDNUT' => $qtdNut,
                'USU_NUMEME' => NULL,
                'USU_SEQEME' => NULL,
                'USU_CODDEP' => NULL,
                'USU_DATMOV' => NULL,
                'USU_SEQMOV' => NULL,
                'USU_CTARED' => $ctaFin,
                'USU_USUGER' => $codUsu,
                'USU_DATGER' => $now,
                'USU_HORGER' => $horSis
            ]);
    }
}
