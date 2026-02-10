<?php

namespace Modules\Producao\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MovimentacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('producao::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('producao::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('producao::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('producao::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }

    public function salvarmovimentacao(Request $request)
    {
        // Receber dados do formulário
        $codMov = $request->input('codMov');
        $numOrp = $request->input('numOrp');

        // Passo 1: Recuperar movimentação
        $movimentacao = $this->getMovimentacaoDg($codMov);

        if ($movimentacao->isEmpty()) {
            return response()->json(['error' => 'Movimentação não encontrada'], 404);
        }

        // Passo 2: Checar se a movimentação bate com a estação criada
        if (!$this->checkmovwithsta($codMov, $numOrp)) {
            return redirect()->route('falha', ['numOrp' => $numOrp])->with('message', 'Estação não aceita para esta movimentação');
        }

        // Passo 3: Inserir itens da movimentação
        $session = auth()->user() ?? (object)['id' => null]; // Exemplo de sessão autenticada
        $this->additensmov($codMov, $numOrp, $session);


        //Passo 4: Altera a situação da ordem de produção para Finalizada
        return redirect()->route('finalizarOrdem', ['codMov' => $codMov, 'numOrp' => $numOrp]);
    }

    private function getMovimentacaoDg($codMov)
    {
        return DB::table('USU_TMOVDG')
            ->select('USU_CODMOV')
            ->where('USU_CODMOV', $codMov)
            ->get();
    }

    private function checkmovwithsta($codMov, $numOrp)
    {
        // Pegar ordem e verificar se a movimentação aceitará esta station
        $ordens = DB::table('usu_tkaepap')
            ->where('usu_numorp', $numOrp)
            ->get();

        $codStation = null;
        foreach ($ordens as $ordem) {
            $codStation = $ordem->usu_codsta;
        }

        /*// Verificar se a estação bate com o código da movimentação
        $movimentacoes = DB::table('usu_tmovdg')
            ->where('usu_stamov', $codStation)
            ->where('usu_codmov', $codMov)
            ->get();*/

        $movimentacoes = DB::table('usu_tmovdg as t')
            ->where('t.usu_codmov', $codMov)
            ->whereExists(function ($q) use ($codStation) {
                $q->select(DB::raw(1))
                    ->from(DB::raw("STRING_SPLIT(t.usu_stamov, ',') AS s"))
                    ->whereRaw('LTRIM(RTRIM(s.value)) = ?', [$codStation]);
            })
            ->get();

        return !$movimentacoes->isEmpty();
    }

    private function additensmov($codMov, $numOrp, $session)
    {
        $areaSheets = DB::table('usu_tkaeppr')
            ->join('usu_tkaepdg', function ($join){
                $join->on('usu_tkaeppr.usu_codemp', '=', 'usu_tkaepdg.usu_codemp')
                    ->on('usu_tkaeppr.usu_codfil', '=', 'usu_tkaepdg.usu_codfil')
                    ->on('usu_tkaeppr.usu_numorp', '=', 'usu_tkaepdg.usu_numorp')
                    ->on('usu_tkaeppr.usu_seqorp', '=', 'usu_tkaepdg.usu_seqorp')
                    ->on('usu_tkaeppr.usu_codver', '=', 'usu_tkaepdg.usu_codver');
            })
            ->join('usu_tkaepap', function ($join) {
                $join->on('usu_tkaeppr.usu_codemp', '=', 'usu_tkaepap.usu_codemp')
                    ->on('usu_tkaeppr.usu_codfil', '=', 'usu_tkaepap.usu_codfil')
                    ->on('usu_tkaeppr.usu_numorp', '=', 'usu_tkaepap.usu_numorp')
                    ->on('usu_tkaeppr.usu_seqorp', '=', 'usu_tkaepap.usu_seqorp')
                    ->on('usu_tkaeppr.usu_codver', '=', 'usu_tkaepap.usu_codver');
            })
            ->where('usu_tkaeppr.usu_numorp', $numOrp)
            ->where('usu_tkaepdg.usu_sitorp', '<>', 3)
            ->where('usu_tkaeppr.usu_seqper', 1)
            ->get();

        $param = DB::table('usu_tkaeppr')
            ->where('usu_numorp', $numOrp)
            ->first();

        foreach ($areaSheets as $areaSheet) {

            $data = [
                'usu_codmov' => $codMov,
                'usu_codid' => $areaSheet->usu_codid,
                'usu_unimed' => $areaSheet->usu_unimed,
                'usu_qtduni' => (float)$areaSheet->usu_qtdper,
                'usu_numorp' => $areaSheet->usu_numorp,
                'usu_codver' => $areaSheet->usu_codver,
                'usu_codfil' => 1,
                'usu_codemp' => 1,
                'usu_codfam' => substr($areaSheet->usu_codmnr, 0, 9),
                'usu_codmnr' => $areaSheet->usu_codmnr,
                'usu_codsta' => $areaSheet->usu_codsta,
                'usu_usuger' => $session->id,
                'usu_horger' => null,
                'usu_datger' => Carbon::now()->format('Y-m-d'),
                'usu_tipite' => 1,
                'usu_matmon' => null,
                'usu_param' => $param->usu_desper ?? null,
            ];

            DB::table('usu_tmovite')->insert($data);
        }
    }

    public function finalizarOrdem($codMov, $numOrp)
    {
        DB::beginTransaction();

        try {
            $itemExiste = DB::table('usu_tmovite')
                ->where('usu_numorp', $numOrp)
                ->where('usu_codmov', $codMov)
                ->exists();

            if (!$itemExiste) {
                return response()->json(['error' => "Movimentação $codMov e Ordem $numOrp não encontrado na tabela de movimentação!"], 404);
            }

            DB::table('usu_tkaepdg')
                ->where('usu_numorp', $numOrp)
                ->where('usu_sitorp', '<>', 3)
                ->update(['usu_sitorp' => 4]);

            DB::commit(); // Confirma a transação
            // Redirecionar para a página de sucesso
            return redirect()->route('sucesso')->with('message', 'Movimentação e Ordem finalizada com sucesso! Pode encerrar esta página');

        } catch (\Exception $e) {
            DB::rollBack(); // Reverte a transação em caso de erro
            return redirect()->route('falha', ['numOrp' => $numOrp])->with('message', 'Erro ao finalizar a ordem ');
        }
    }

    public function paginaSucesso()
    {

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sucesso</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Roboto", Arial, sans-serif;
            background: linear-gradient(135deg, #1c92d2, #f2fcfe);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #333;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            max-width: 480px;
            width: 100%;
            padding: 30px;
            text-align: center;
        }
        .card h1 {
            font-size: 2.5rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        .card p {
            font-size: 1.2rem;
            color: #555;
            margin-bottom: 30px;
        }
        .card button {
            display: inline-block;
            padding: 12px 25px;
            font-size: 1rem;
            color: #fff;
            background: #28a745;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .card button:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Sucesso!</h1>
        <p>Sua movimentação e ordem foi finalizada com sucesso! </p>
        <p>Pode fechar esta aba!</p>
    </div>
</body>
</html>
HTML;

        return response($html)->header('Content-Type', 'text/html');
    }

    public function paginaFalha($numOrp)
    {
        $message = session('message', '');
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: "Roboto", Arial, sans-serif;
            background: linear-gradient(135deg, #f5e6e8, #f8d3cf);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            color: #333;
        }
        .card {
            background: rgba(255, 255, 255, 0.8); /* Fundo translúcido */
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
            padding: 30px;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        .card h1 {
            font-size: 2rem;
            color: #b30000; /* Um vermelho escuro menos agressivo */
            margin-bottom: 15px;
        }
        .card p {
            font-size: 1rem;
            color: #555;
            margin-bottom: 20px;
        }
        .card a {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1rem;
            color: #fff;
            background: #b30000; /* Botão em vermelho escuro */
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .card a:hover {
            background: #930000; /* Mais escuro no hover */
            transform: translateY(-2px); /* Leve elevação no hover */
        }
        .card-info {
            font-size: 0.9rem;
            color: #999;
            margin-top: 15px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Erro!</h1>
        <p>Algo deu errado na movimentação com o número da ordem: <strong>{$numOrp}</strong>.</p>
        <p class="card-info"><strong>Mensagem:</strong> {$message}</p> <!-- Mostra a mensagem dinâmica -->
        <a href="https://erpweb.knapp.at/producao/handlingunit/{$numOrp}">Tente Novamente</a>
        <p class="card-info">Se o problema persistir, entre em contato com o suporte.</p>
        <br>
    </div>
</body>
</html>
HTML;

        return response($html)->header('Content-Type', 'text/html');
    }
}
