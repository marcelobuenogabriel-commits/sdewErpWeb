<?php

namespace Modules\Picking\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Picking\App\Models\PickingProducao;
use Modules\WebService\App\Http\Controllers\WebServicePickingController;

class InsumosController extends Controller
{
    protected $table;

    protected $hour;

    protected $codDep;
    protected $codTns;

    protected $codEmp;

    protected $functions;

    public function __construct()
    {
        $this->middleware('auth');
        $this->table = new PickingProducao();
        $this->functions = new PickingController();
        $this->hour = date('h:i');

        $this->codEmp = 1;
        $this->codDep = '116';
        $this->codTns = '90289';
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('picking::index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $codUsu = auth()->user()->codusu;
        $codPro = $request->codPro;
        $qtdMov = $request->qtdMov;
        $codDer = '';

        $return_picking = $this->picking($codPro, $qtdMov, $codUsu, $codDer);

        if($return_picking == true) {
            $msg_return = 'Processado com sucesso!';

        } else if($return_picking == false) {
            $msg_return = 'Erro ao processar requisição, tente novamente.';

        }

        return \response()->json($msg_return);
    }

    protected function picking($codPro, $qtdMov, $codUsu, $codDer)
    {
        /*
         * Válida as informações enviadas pelo Coletor
         */
        if ($codPro == NULL || $qtdMov == NULL) {
            return 'Código de barras inválido!';
        }

        /*
         * Consulta Posição do estoque para Separação
         */
        $qtdEst = $this->table->consultStock($this->codEmp, $codPro, $this->codDep, $qtdMov);

        if ($qtdEst) {
            $pickingService = new WebServicePickingController();

            /*
             * Converte a Unidade de medida de (.) para (,)
             */
            $qtdPro = $this->functions->convertUnid($qtdMov);

            return $pickingService->execActionSid($codPro, $this->codDep, $qtdPro, NULL, NULL, $codUsu, $this->codTns, $codDer);
        } else {
            return 'Quantidade indisponível em estoque.';
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('picking::insumos.index');
    }
}
