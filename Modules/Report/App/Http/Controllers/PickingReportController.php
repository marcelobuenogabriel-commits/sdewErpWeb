<?php

namespace Modules\Report\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\WebService\App\Http\Controllers\WebServiceController;

class PickingReportController extends Controller
{

    private $web_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->web_service = new WebServiceController();
    }

    public function SWOC001View()
    {
        return view('report::picking.swoc001');
    }

    public function SWOC001(Request $request)
    {
        $numOcp = (int) $request->numOcp;

        if(empty($numOcp)) {
            session()->flash('error', 'Obrigatório informar o número da Ordem de Compra');
            return \response()->json();
        }

        /*
         * Utilizar o WebService padrão para envio de requisições.
         *  prPrintDest = Impressora de Destino
         *  prRelatório = Relatório referência do sistema
         *  prEntrada = Parâmetros de entrada do relatório
         */
        $parans = [
            'prPrintDest' => '\\\\knbrpr01\\prbr010',
            'prExecFmt' => 'tefPrint',
            'prRelatorio' => 'SRNF212.GER',
            'prEntrada' => '<![CDATA[<ECodEmp=1><ECodFil=1><ENumOcp='.$numOcp.'>]]>',
            'prEntranceIsXML' => 'F'
        ];

        return $this->web_service->printReport($parans);
    }

    public function SWOC002View()
    {
        return view('report::picking.swoc002');
    }

    public function SWOC002(Request $request)
    {
        $numOcp = (int) $request->numOcp;
        $seqOcp = (int) $request->seqOcp;
        $qtdImp = (int) $request->qtdImp;

        if(empty($numOcp) || empty($seqOcp)) {
            session()->flash('error', 'Todos os campos são obrigatórios.');
            return \response()->json();
        }

        if($qtdImp == 0) {
            session()->flash('error', 'Quantidade de impressões deve ser maior que 0');
            return \response()->json();
        }
        /*
         * Utilizar o WebService padrão para envio de requisições.
         *  prPrintDest = Impressora de Destino
         *  prRelatório = Relatório referência do sistema
         *  prEntrada = Parâmetros de entrada do relatório
         */

        $parans = [
            'prPrintDest' => '\\\\knbrpr01\\prbr010',
            'prExecFmt' => 'tefPrint',
            'prRelatorio' => 'SRNF212.GER',
            'prEntrada' => '<![CDATA[<ECodEmp=1><ECodFil=1><ENumOcp='.$numOcp.'><eSeqIpo='.$seqOcp.'><eQtdImp='.$qtdImp.'>]]>',
            'prEntranceIsXML' => 'F'
        ];

        return $this->web_service->printReport($parans);
    }

    public function SWOC003View()
    {
        return view('report::picking.swoc003');
    }

    public function SWOC003(Request $request)
    {
        $numMov = (int) $request->numMov;

        if(empty($numMov)) {
            session()->flash('error', 'Todos os campos são obrigatórios.');
            return \response()->json();
        }

        /*
         * Utilizar o WebService padrão para envio de requisições.
         *  prPrintDest = Impressora de Destino
         *  prRelatório = Relatório referência do sistema
         *  prEntrada = Parâmetros de entrada do relatório
         */

        $parans = [
            'prPrintDest' => '\\\\knbrpr01\\prbr010',
            'prExecFmt' => 'tefPrint',
            'prRelatorio' => 'DSPS220.GER',
            'prEntrada' => '<![CDATA[<aCodMov='.$numMov.'>]]>',
            'prEntranceIsXML' => 'F'
        ];

        return $this->web_service->printReport($parans);
    }

    public function SWOC004View(Request $request)
    {
        $printer = Session::get('id');
        return view('report::picking.swoc004', compact('printer'));
    }

    public function SWOC004(Request $request)
    {
        $numPro = $request->numPro;
        $codPro = $request->codPro;
        $codDep = $request->codDep;
        $qtdPct = $request->qtdPct;

        $codImp = $request->printer;

        if(empty($numPro) || empty($codPro) || empty($codDep) || empty($qtdPct)) {
            session()->flash('error', 'Todos os campos são obrigatórios.');
            return \response()->json();
        }

        /*
         * Utilizar o WebService padrão para envio de requisições.
         *  prPrintDest = Impressora de Destino
         *  prRelatório = Relatório referência do sistema
         *  prEntrada = Parâmetros de entrada do relatório
         */

        $parans = [
            'prPrintDest' => '\\\\knbrpr01\\'.$codImp,
            'prExecFmt' => 'tefPrint',
            'prRelatorio' => 'SRNF111.GER',
            'prEntrada' => '<![CDATA[<ENomPrj='.$numPro.'><ECodPro='.$codPro.'><EQtdPct='.$qtdPct.'><ECodDep='.$codDep.'>]]>',
            'prEntranceIsXML' => 'F'
        ];

        return $this->web_service->printReport($parans);
    }

    public function SWOC005View()
    {
        return view('report::picking.swoc005');
    }

    public function SWOC005(Request $request)
    {
        $seqPro = $request->seqPro;

        if(empty($seqPro)) {
            session()->flash('error', 'Todos os campos são obrigatórios.');
            return \response()->json();
        }

        /*
         * Utilizar o WebService padrão para envio de requisições.
         *  prPrintDest = Impressora de Destino
         *  prRelatório = Relatório referência do sistema
         *  prEntrada = Parâmetros de entrada do relatório
         */

        $parans = [
            'prPrintDest' => '\\\\knbrpr01\\PRBR010',
            'prExecFmt' => 'tefPrint',
            'prRelatorio' => 'SRNF116.GER',
            'prEntrada' => '<![CDATA[<ECodEmp=1><ECodFil=1><ESeqPro='.$seqPro.'>]]>',
            'prEntranceIsXML' => 'F'
        ];

        return $this->web_service->printReport($parans);
    }
}
