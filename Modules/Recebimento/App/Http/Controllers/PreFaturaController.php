<?php

namespace Modules\Recebimento\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Recebimento\App\Models\PreFatura;
use Modules\WebService\App\Http\Controllers\WebServicePreFaturaController;

class PreFaturaController extends Controller
{
    protected $table;

    protected $webServicePreFatura;

    public function __construct()
    {
        $this->middleware('auth');
        $this->table = new PreFatura();
        $this->webServicePreFatura = new WebServicePreFaturaController();
    }

    public function index()
    {
        $prefaturas = $this->table->getPrefaturas();
        return view('recebimento::prefatura.index', compact('prefaturas'));
    }

    public function gerarEmbalagemPreFatura(Request $request): RedirectResponse
    {
        $numane = $request->input('numane');
        $numpfa = $request->input('numpfa');
        $codemb = $request->input('codemb');
        $obsemb = $request->input('obsemb');
        $pesbru = $request->input('pesbru');
        $pesliq = $request->input('pesliq');
        $qtdpfa = $request->input('qtdpfa');
        $codpro = $request->input('selected_ids', []);

        $produtos = explode(',', $codpro);
       
        $prefatura = $this->table->getPrefatura($numane, $numpfa);

        $resultProdutos = $prefatura->filter(function ($item) use ($produtos) {
            return in_array($item->codpro, $produtos);
        })->values();

        $response = $this->webServicePreFatura->gerarEmbalagemPreFatura([],[
            'numane' => $numane,
            'numpfa' => $numpfa,
            'codemb' => $codemb,
            'obsemb' => $obsemb,
            'pesbru' => $pesbru,
            'pesliq' => $pesliq,
            'qtdpfa' => $qtdpfa,
            'result' => $resultProdutos
        ]);

        if ($response->status() == 401) {
            $error = $response->original['error'];
            session()->flash('error', 'Erro ao gerar embalagens para pré-fatura: ' . $error);

            return redirect()->route('recebimento.prefatura');
        } 

        if ($response->status() == 400) {
            $error = $response->original['message'];
            session()->flash('error', 'Erro ao gerar embalagens para pré-fatura: ' . $error);

            return redirect()->route('recebimento.prefatura');
        }

        session()->flash('success', 'Embalagens para pré-fatura geradas com sucesso.');

        return redirect()->route('recebimento.prefatura');
    }

    public function show($numane, $numpfa)
    {
        $produtos = $this->table->getProdutosPrefatura($numane, $numpfa);

        $prefatura = $this->table->getPrefatura($numane, $numpfa);

        if ($prefatura->isEmpty()) {
            session()->flash('error', 'Pré-fatura não encontrada.');
            return redirect()->route('recebimento.prefatura');
        }

        $prefatura = collect($prefatura);
        $produtos = collect($produtos)->keyBy('codpro');
   
        if ($prefatura->isEmpty()) {
            session()->flash('error', 'Pré-fatura não encontrada.');
            return redirect()->route('recebimento.prefatura');
        }

        $produtos = $prefatura->filter(function ($item) use ($produtos) {

            $codpro = $item->codpro;
            if ($produtos->has($codpro)) {
                $qtdemb = $produtos->get($codpro)->qtdpro;

                if ($qtdemb == $item->qtdppf) {
                    return false;
                } else {
                    $item->qtdppf = $item->qtdppf - $qtdemb;
                    $item->qtdemb = $qtdemb;
                }
            }

            return $item;
        })->values();

        return view('recebimento::prefatura.show', compact('produtos', 'numane', 'numpfa'));
    }

    public function impressaoEtiquetas()
    {
        $response = $this->webServicePreFatura->etiqueta();

        if ($response->status() == 200) {
            session()->flash('success', 'Impressão de etiquetas iniciada com sucesso.');
        } else {
            $error = $response->original['error'];
            session()->flash('error', 'Erro ao iniciar impressão de etiquetas: ' . $error);
        }

        return redirect()->route('recebimento.prefatura');
    }
}
