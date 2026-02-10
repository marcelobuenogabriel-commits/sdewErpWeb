<?php

namespace Modules\Financeiro\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Financeiro\App\Http\Requests\InvoicePedidoRequest;
use Modules\Financeiro\App\Models\Invoice;
use Modules\Financeiro\App\Models\Pedidos;
use Modules\WebService\App\Http\Controllers\WebServiceController;
use function PHPUnit\Framework\isEmpty;

class InvoiceController extends Controller
{
    private $table;

    private $table_pedidos;

    protected $table_invoice;

    protected $web_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->table = new Invoice();
        $this->table_pedidos = new Pedidos();
        $this->table_invoice = new Invoice();
        $this->web_service = new WebServiceController();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /*
         * Consulta a origem da requisição
         *  1 - Pedidos
         *  2 - Contratos
         */
        $tipo = $request->segment('3') == NULL ? key($request->query()) : $request->segment('3');

        if ($tipo == 'pedidos') {
            $pedidos = $this->table->findAllPedidos();
            return view('financeiro::invoice.pedidos.index', compact('pedidos'), [
                'title' => 'Pedidos',
                'description' => 'Listagem de Invoices geradas para Pedidos',
            ]);

        } else if ($tipo == 'contratos') {
            $contratos = $this->table->findAllContratos();
            return view('financeiro::invoice.contratos.index', compact('contratos'), [
                'title' => 'Contratos',
                'description' => 'Listagem de Invoices geradas para Contratos',
            ]);

        }

        return view('financeiro::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $invoice = [];
        /*
         * Consulta a origem da requisição
         *  1 - Pedidos
         *  2 - Contratos
         */
        $tipo = array_key_first($request->query());

        if ($tipo == 'pedidos') {
            $pedidos = [];
            return view('financeiro::invoice.pedidos.create', compact('pedidos', 'invoice'));

        } else if ($tipo == 'contratos') {
            $contratos = [];
            return view('financeiro::invoice.contratos.create', compact('contratos'));

        }

        return view('financeiro::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(InvoicePedidoRequest $request): RedirectResponse
    {
        $invoice = $this->table_invoice->getUltimoRegistro();
        $codUsu = auth()->user()->codusu;

        /*
         * Gera um auto-increment para o número da Invoice
         */
        $new = (int)$invoice->usu_codinv + 1;

        if ($request->codPed) {
            /*
            * Consulta se existe Invoice para o número de Pedido informado
            */
            $version = $this->table_invoice->getVersionInvoicePedido($request);

            if($version) {
                $version = $version->numVer + 1;
            } else {
                $version = 1;
            }

            $this->table_invoice->addInvoicePedido($request, $new, $version, $codUsu);

            return redirect()->route('invoice.index', 'pedidos')->with('success', 'Invoice ' . $new . ' criada com sucesso');
        } else if ($request->numCtr) {
            /*
            * Consulta se existe Invoice para o número do Contrato informado
            */
            $version = $this->table_invoice->getVersionInvoiceContrato($request);

            if($version) {
                $version = $version->numVer + 1;
            } else {
                $version = 1;
            }

            $this->table_invoice->addInvoiceContrato($request, $new, $version, $codUsu);

            return redirect()->route('invoice.index', 'contratos')->with('success', 'Invoice ' . $new . ' criada com sucesso');
        }

        return redirect()->route('invoice.index', 'pedidos')->with('success', 'Invoice ' . $new . ' criada com sucesso');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('financeiro::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        if (empty($id)) {
            return redirect()->back()->with('error', 'Invoice não localizada, tente novamente.');
        }

        $invoice = $this->table_invoice->findInvoice($id);

        if ($invoice->notCre == 1) {
            $invoice->notCre = '1';
        } else {
            $invoice->notCre = '2';
        }

        if ($invoice->tipInv == 2) {
            $pedido = $this->table_pedidos->getPedido($invoice->codEmp, $invoice->numPed)->toArray();

            foreach ($pedido as $ped) {
                $pedidos = [
                    'id' => $ped->NUMPED,
                    'numped' => $ped->NUMPED . ' - ' . $ped->NOMCLI
                ];
            }

            return view('financeiro::invoice.pedidos.edit', compact('invoice', 'pedidos'));
        } else if ($invoice->tipInv == 1) {
            $contrato = $this->table_pedidos->getContrato($invoice->codEmp, $invoice->numPed)->toArray();

            if (empty($contrato)) {
                return redirect()->route('inv-contratos')->with('error', 'Contrato não localizado!');
            } else {

                foreach ($contrato as $ctr) {
                    $contratos = [
                        'id' => $ctr->NUMCTR,
                        'numctr' => $ctr->NUMCTR . ' - ' . $ctr->NOMCLI
                    ];
                }

                return view('financeiro::invoice.contratos.edit', compact('invoice', 'contratos'));
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(InvoicePedidoRequest $request, $id): RedirectResponse
    {
        $codUsu = auth()->user()->codusu;
        $invoice = $this->table_invoice->findInvoice($id);

        if ($invoice->tipInv == 2) {
            $this->table_invoice->editInvoicePedido($request, $id, $codUsu, $invoice);
            return redirect()->route('invoice.index', 'pedidos')->with('success', 'Invoice ' . $id . ' atualizada.');
        } else if ($invoice->tipInv == 1) {
            $this->table_invoice->editInvoiceContrato($request, $id, $codUsu, $invoice);
            return redirect()->route('invoice.index', 'contratos')->with('success', 'Invoice ' . $id . ' atualizada.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function emiteInvoice(Request $request)
    {
        $codInv = $request->codInv;
        $email_knapp = $request->emailKnapp;
        $email_remetente = $request->email;
        $tipo_invoice = $request->tipInv;
        $data_cobranca = $request->dataCobranca;

        $data_cobranca = date('d/m/Y', strtotime($data_cobranca));

        /*
         * Utilizar o WebService padrão para envio de requisições.
         *  prRelatório = Relatório referência do sistema
         *  prEntrada = Parâmetros de entrada do relatório
         */

        /*
         * Invoices
         *  Tipo 1 = Contratos
         *  Tipo 2 = Pedidos
         */
        if ($tipo_invoice == 1) {
            $relatorio = 'RFNF205.GER';
            $prEntrada = '<![CDATA[<ECodInv=' . $codInv . '><EEMail=' . $email_remetente . '><EDatIni=' . $data_cobranca . '>]]>';
        } else if ($tipo_invoice == 2) {
            $relatorio = 'RFNF207.GER';
            $prEntrada = '<![CDATA[<ECodInv=' . $codInv . '><EEMail=' . $email_remetente . '>]]>';
        }

        $parans = [
            'prExecFmt' => 'tefMail',
            'prRelatorio' => $relatorio,
            'prRemetente' => 'no-reply@senior.com.br',
            'prFileName' => 'Invoice - ' . $codInv,
            'prDest' => $email_knapp,
            'prAssunto' => 'Invoice ' . $codInv . ' gerada via sistema',
            'prAnexoBool' => 'T',
            'prSaveFormat' => 'tsfPDF',
            'prEntrada' => $prEntrada,
            'prEntranceIsXML' => 'F'
        ];

        return $this->web_service->printReportByEmail($parans);
    }

    public function consultaPedidos(Request $request)
    {
        $codEmp = $request->codEmp;

        $pedidos = $this->table_pedidos->getPedidos($codEmp);

        return \Response::json($pedidos);
    }

    public function consultaPedido(Request $request)
    {
        $codEmp = $request->codEmp;
        $codPed = $request->codPed;

        $pedido = $this->table_pedidos->getPedido($codEmp, $codPed);

        return \Response::json($pedido);
    }

    public function consultaContratos(Request $request)
    {
        $codEmp = $request->codEmp;

        $contratos = $this->table_pedidos->getContratos($codEmp);

        return \Response::json($contratos);
    }

    public function consultaContrato(Request $request)
    {
        $codEmp = $request->codEmp;
        $numCtr = $request->numCtr;

        $contrato = $this->table_pedidos->getContrato($codEmp, $numCtr);

        return \Response::json($contrato);
    }
}
