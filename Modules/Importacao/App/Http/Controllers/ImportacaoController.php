<?php

namespace Modules\Importacao\App\Http\Controllers;

use App\Http\Controllers\Controller;
use DragonCode\PrettyArray\Services\Formatters\Json;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Financeiro\App\Models\Invoice;
use Modules\Financeiro\App\Models\Pedidos;
use Modules\Importacao\App\Http\Requests\ImportacaoRequest;
use Illuminate\Http\JsonResponse;
use Modules\WebService\App\Http\Controllers\WebServiceController;
use Maatwebsite\Excel\Facades\Excel;

class ImportacaoController extends Controller
{

    protected $table_invoice;

    protected $table_pedidos;

    protected $web_service;


    public function __construct()
    {
        $this->middleware('auth');
        $this->table_invoice = new Invoice();
        $this->table_pedidos = new Pedidos();
        $this->web_service = new WebServiceController();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedidos = $this->table_invoice->getPedidosImportacao();

        if ($pedidos->isEmpty()) {
            session()->flash('error', 'Não existem pedidos cadastrados no sistema. Verifique com o administrador do sistema.');

            return view('importacao::index')
                ->with('pedidos', $pedidos)
                ->with('title', 'Importação / Exportação')
                ->with('description', 'Invoices de Importação / Exportação');
        }

        return view('importacao::index')
            ->with('pedidos', $pedidos)
            ->with('title', 'Importação / Exportação')
            ->with('description', 'Invoices de Importação / Exportação');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('importacao::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ImportacaoRequest $request)
    {
        $data = $request->all();

        /*
        *   Busca o último código de invoice cadastrado e incrementa em 1 para a nova invoice
        */
        $ultimoRegistro = $this->table_invoice->getUltimoRegistro();
        $data['codInv'] = $ultimoRegistro ? $ultimoRegistro->usu_codinv + 1 : 1;
        
        /*
        *   Busca a última versão da invoice para o pedido e empresa informados
        *   e incrementa em 1 para a nova versão
        */
        $ultimaVersao = $this->table_invoice->getUltimaVersaoInvoiceImportacao($data['codEmp'], $data['codPed']);
        $data['versao'] = $ultimaVersao ? $ultimaVersao->versao + 1 : 1;

        /*
        * Formata os campos monetários para o padrão do banco de dados
        */
        $data['gasTot'] = $this->table_invoice->setValorAttribute($data['gasTot']);
        $data['gasFre'] = $this->table_invoice->setValorAttribute($data['gasFre']);
        $data['gasSeg'] = $this->table_invoice->setValorAttribute($data['gasSeg']);
        $data['gasDin'] = $this->table_invoice->setValorAttribute($data['gasDin']);
        
        if ($this->table_invoice->addInvoiceImportacao($data)) {
            session()->flash('success', 'Invoice de Importação cadastrada com sucesso!');
            return redirect()->route('importacao.index');
        } else {
            session()->flash('error', 'Ocorreu um erro ao cadastrar a Invoice de Importação. Verifique os dados e tente novamente.');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $codInv = (int) $id;

        $invoice = $this->table_invoice->findInvoiceImportacao($codInv);

        if (!$invoice) {
            session()->flash('error', 'Invoice de Importação não encontrada. Verifique os dados e tente novamente.');
            return redirect()->route('importacao.index');
        }

        $pedido = $this->table_pedidos->getPedido($invoice->codEmp, $invoice->numPed)->toArray();

        foreach ($pedido as $ped) {
            $pedidos = [
                'id' => $ped->NUMPED,
                'numped' => $ped->NUMPED . ' - ' . $ped->NOMCLI
            ];
        }
        
        return view('importacao::edit', compact('invoice', 'pedidos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ImportacaoRequest $request, $id)
    {
        $codInv = (int) $id;
        $data = $request->all();

        /*
        * Formata os campos monetários para o padrão do banco de dados
        */
        $data['gasTot'] = $this->table_invoice->setValorAttribute($data['gasTot']);
        $data['gasFre'] = $this->table_invoice->setValorAttribute($data['gasFre']);
        $data['gasSeg'] = $this->table_invoice->setValorAttribute($data['gasSeg']);
        $data['gasDin'] = $this->table_invoice->setValorAttribute($data['gasDin']);

        if ($this->table_invoice->updateInvoiceImportacao($codInv, $data)) {
            session()->flash('success', 'Invoice de Importação atualizada com sucesso!');
            return redirect()->route('importacao.index');
        } else {
            session()->flash('error', 'Ocorreu um erro ao atualizar a Invoice de Importação. Verifique os dados e tente novamente.');
            return redirect()->back()->withInput();
        }
    }

    public function getProdutos($id)
    {
        $codInv = (int) $id;

        $invoice = $this->table_invoice->findInvoiceImportacao($codInv);

        if (!$invoice) {
            session()->flash('error', 'Invoice de Importação não encontrada. Verifique os dados e tente novamente.');
            return redirect()->route('importacao.index');
        }

        $produtos = $this->table_invoice->getProdutosInvoiceImportacao($codInv);

        if (!$produtos) {
            session()->flash('error', 'Produtos da Invoice de Importação não encontrados. Verifique os dados e tente novamente.');
            return redirect()->route('importacao.index');
        }

        $produtos = $produtos->filter(function ($item) use ($codInv) {
            if ($codInv == (int) $item->USU_CODINV) {
                return true;
            } else {
                return $item->QTDABEPED !== '.00';
            }
        })->values();
        
        
        return view('importacao::produtos', compact('invoice', 'produtos'));
    }

    public function updateInvoiceImportacao(Request $request)
    {
        $produtosSelecionados = $request->input('produto_selecionado', []);
        $quantidades = $request->input('qtdped', []);
        $codpai = $request->input('codpai', []);
        $codInv = (int) $request->route('id');
        $numPed = (int) $request->input('codPed');
        $codEmp = (int) $request->input('codEmp');

        $invoice = $this->table_invoice->findInvoiceImportacao($codInv);

        if (!$invoice) {
            session()->flash('error', 'Invoice de Importação não encontrada. Verifique os dados e tente novamente.');
            return redirect()->route('importacao.index');
        }

        
        // Mantém apenas os índices de $quantidades que estão em $produtosSelecionados
        $arrayProdutos = array_intersect_key($quantidades, array_flip($produtosSelecionados));

        foreach ($arrayProdutos as $produtoId => $quantidade) {
            // Atualiza a quantidade do produto na invoice
            $ultimoRegistro = $this->table_invoice->getUltimoRegistroProdutos();
            $ultimoRegistro = $ultimoRegistro ? $ultimoRegistro->usu_numite + 1 : 1;

            $paisOrigem = $codpai[$produtoId]; // Obtém o país de origem do array de produtos selecionados

            $this->table_invoice->addProdutosInvoiceImportacao($codEmp, $numPed, $codInv, $produtoId, $quantidade, $ultimoRegistro, $paisOrigem);
        }

        if (empty($arrayProdutos)) {
            session()->flash('error', 'Nenhum produto foi selecionado. Por favor, selecione ao menos um produto para adicionar à Invoice de Importação.');
            return redirect()->back();
        }
        
        session()->flash('success', 'Produtos adicionados à Invoice de Importação com sucesso!');
        return redirect()->route('importacao.index');
    }

    public function deleteProdutoInvoice($id, $produtoId): JsonResponse
    {
        $codInv = (int) $id;
        $produtoId = $produtoId;

        $invoice = $this->table_invoice->findInvoiceImportacao($codInv);

        if (!$invoice) {
            return response()->json(['message' => 'Ocorreu um erro ao encontrar a Invoice de Importação. Verifique os dados e tente novamente.'], 500);
        }
        
        if ($this->table_invoice->deleteProdutoInvoiceImportacao($codInv, $produtoId)) {
            return response()->json(['message' => 'Produto removido da Invoice de Importação com sucesso!'], 200);
        } else {
            return response()->json(['message' => 'Ocorreu um erro ao remover o produto da Invoice de Importação. Verifique os dados e tente novamente.'], 500);
        }
    }

    public function emiteInvoice(Request $request): JsonResponse
    {
        $codInv = (int) $request->input('codInv');
        $emailKnapp = $request->input('emailKnapp');
        $aContDst = $request->input('aContDst');
        $aContPre = $request->input('aContPre');
        $aContAqu = $request->input('aContAqu');
        $aWood = $request->input('aWood');
        $nMostrarNcm = $request->input('nMostrarNcm');
        $numVer = $request->input('numVer');

        $invoice = $this->table_invoice->findInvoiceImportacao($codInv);

        if (!$invoice) {
            return response()->json(['message' => 'Ocorreu um erro ao encontrar a Invoice de Importação. Verifique os dados e tente novamente.'], 500);
        }

        $relatorio = 'DSPS224.GER';
        $prEntrada = '<![CDATA[<aCodEmp=' . 1 . '><aCodfil=' . 1 . '><aNumInv=' . $codInv . '><aContDst=' . $aContDst . '><aContPre=' . $aContPre . '>
        <aContAqu=' . $aContAqu . '><aWood=' . $aWood . '><aCodVer=' . $numVer . '><nMostrarNcm=' . $nMostrarNcm . '>]]>';

        $parans = [
            'prExecFmt' => 'tefMail',
            'prRelatorio' => $relatorio,
            'prRemetente' => 'no-reply@senior.com.br',
            'prFileName' => 'Invoice - ' . $codInv,
            'prDest' => $emailKnapp,
            'prAssunto' => 'Invoice ' . $codInv . ' gerada via sistema',
            'prAnexoBool' => 'T',
            'prSaveFormat' => 'tsfPDF',
            'prEntrada' => $prEntrada,
            'prEntranceIsXML' => 'F'
        ];

        return $this->web_service->printReportByEmail($parans);
    }    

    public function importDocument(Request $request)
    {
        $file = $request->file('import_document');
        $codInv = (int) $request->input('codInv');
        $quantidadeLinhas = 0;
        $linhasProcessadas = 0;

        try {
            $sheets = Excel::toCollection(null, $file);
            $rows = $sheets->first();

            foreach ($rows as $index => $row) {

                if ($index === 0) {
                    // Pula o cabeçalho
                    continue;
                }

                $quantidadeEntregue = $row[7] ?? null;
                $produto = $row[10] ?? null;

                $produto = str_replace('_', '', $produto);

                if ($quantidadeEntregue == null || $produto == null) {
                    break;
                }

                $produtosImportados[] = [
                    'codInv' => $codInv,
                    'produtoId' => $produto,
                    'quantidade' => (float) str_replace(',', '.', $quantidadeEntregue),
                ];

                $quantidadeLinhas += 1;
            }

            $invoice = $this->table_invoice->findInvoiceImportacao($codInv);

            if (!$invoice) {
                session()->flash('error', 'Invoice de Importação não encontrada. Verifique os dados e tente novamente.');
                return redirect()->route('importacao.index');
            }

            $produtos = $this->table_invoice->getProdutosInvoiceImportacao($codInv);

            // cria lookup por CODPRO para busca rápida
            $produtosByCod = $produtos->keyBy(function ($item) {
                return (string) $item->CODPRO;
            });

            $produtosExistentes = [];

            if (!empty($produtosImportados)) {
                foreach ($produtosImportados as $imp) {
                    $codPro = (string) $imp['produtoId'];
                    if (isset($produtosByCod[$codPro])) {
                        $item = clone $produtosByCod[$codPro];
                        $ultimoRegistro = $this->table_invoice->getUltimoRegistroProdutos();
                        $item->qtdEnt = $imp['quantidade'];
                        $item->ultReg = $ultimoRegistro ? $ultimoRegistro->usu_numite + 1 : 1;

                        $produtosExistentes[] = $item;
                        $linhasProcessadas++;
                    }
                }
            }

            // Se nenhum produto casou, retorna 404
            if (count($produtosExistentes) === 0) {
                return response()->json(['message' => 'Nenhum produto encontrado para a Invoice de Importação.', 'linhasProcessadas' => $linhasProcessadas, 'quantidadeLinhas' => $quantidadeLinhas], 404);
            }

            // Adiciona os produtos importados à invoice
            foreach ($produtosExistentes as $produto) {
                $this->table_invoice->addProdutosInvoiceImportacao(
                    $invoice->codEmp,
                    $invoice->numPed,
                    $codInv,
                    $produto->SEQIPD,
                    $produto->qtdEnt,
                    $produto->ultReg
                );
            }

            return response()->json(['message' => 'Foram processadas '.$linhasProcessadas.' de um total de '.$quantidadeLinhas, 'linhasProcessadas' => $linhasProcessadas, 'quantidadeLinhas' => $quantidadeLinhas], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao importar: ' . $e->getMessage()], 500);
        }
    }

    public function getEndereco(Request $request)
    {
        $codInv = (int) $request->input('id');

        $invoice = $this->table_invoice->findInvoiceImportacao($codInv);

        $endereco = $this->table_invoice->getEnderecoInvoiceImportacao($invoice->codEmp, $codInv);

        if (!$invoice) {
            session()->flash('error', 'Invoice de Importação não encontrada. Verifique os dados e tente novamente.');
            return redirect()->route('importacao.index');
        }

        return view('importacao::endereco', compact('invoice', 'endereco'));
    }

    public function updateEndereco(Request $request, $id): RedirectResponse
    {
        $codInv = (int) $id;
        $codEmp = (int) $request->input('codEmp');
        $data = $request->all();

        $invoice = $this->table_invoice->findInvoiceImportacao($codInv);
        
        $endereco = $this->table_invoice->getEnderecoInvoiceImportacao($codEmp, $codInv);

        if (!$invoice) {
            session()->flash('error', 'Invoice de Importação não encontrada. Verifique os dados e tente novamente.');
            return redirect()->route('importacao.index');
        }

        if (!$endereco) {
            $this->table_invoice->addEnderecoInvoiceImportacao($codEmp, $codInv, $data);
            session()->flash('success', 'Endereço da Invoice de Importação cadastrado com sucesso!');
            return redirect()->route('importacao.index');
        }

        if ($this->table_invoice->updateEnderecoInvoiceImportacao($codInv, $data)) {
            session()->flash('success', 'Endereço da Invoice de Importação atualizado com sucesso!');
            return redirect()->route('importacao.index');
        } else {
            session()->flash('error', 'Ocorreu um erro ao atualizar o endereço da Invoice de Importação. Verifique os dados e tente novamente.');
            return redirect()->back()->withInput();
        }
    }

}
