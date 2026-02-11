<?php

namespace Modules\Picking\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\Picking\App\Http\Requests\PickingDispatchRequest;
use Modules\Picking\App\Models\PickingDispatch;
use Modules\Picking\App\Models\PickingProducao;
use Modules\WebService\App\Http\Controllers\WebServicePickingController;

class DispatchController extends Controller
{
    protected $table;

    protected $date;

    protected $hour;

    protected $table_picking;

    protected $functions;

    public function __construct()
    {
        $this->middleware('auth');
        $this->table = new PickingDispatch();
        $this->table_picking = new PickingProducao();
        $this->functions = new PickingController();

        $this->date = date('Y-m-d');
        $this->hour = date('h:i');
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
    public function store(PickingDispatchRequest $request)
    {
        /**
         * Validação se o Submit é para criação de uma nova movimentação
         */
        if ($request->create) {

            $project = (string)$request->numpro;
            $numPro = 'Z119-00' . $project;
            $codUsu = auth()->user()->codusu;

            $newMov = $this->table->findLastMov();

            /**
             * Adiciona a última movimentação na tabela USU_TMOVDG e USU_TMOVEMB
             */
            $newMov = $newMov + 1;

            $this->table->insertNewMovi($numPro, $this->date, $newMov, $codUsu);

            Session::put(['idmov' => $newMov]);

            return view('picking::dispatch.dispatch');
        } else {

            $codmov = $request->codmov;
            $numMov = $this->table->findMovByCodMov($codmov);

            if (!$numMov) {
                return redirect()->back()->with('error', 'Movimentação não localizada.');
            }

            Session::put(['idmov' => $numMov->CODMOV]);

            return view('picking::dispatch.dispatch');
        }
    }

    public function storeDispatch(Request $request)
    {
        $codUsu = auth()->user()->codusu;
        $codSep = $request->codSep;
        $ccuIni = $request->ccuIni;
        $codMov = $request->codMov;
        $codDer = '';

        if ($ccuIni == '5850') {

            /*
             * Busca a quantidade Total agrupado por LT, sem agrupamento por família
             */
            $info_order = $this->table->getQtdTotItem($codSep, $ccuIni);

            if(!$info_order) {
                return 'Produto não localizado!';
            } else {

                $codEmp = $info_order->CODEMP;
                $numPro = $info_order->NUMPRO;
                $qtdMov = $info_order->QTDPCK;
                $codPro = $info_order->CODPCK;
                $uniMed = $info_order->UNIMED;
                $numLt = $info_order->NUMLT;
                $date = $this->date;
                $hour = $this->functions->convertHours();
                $codTns = '90259';

                /*
                 * Finaliza as ordens referente ao Código de Separador informado
                 */
                $closeOrders = $this->table->closeOrder($codEmp, $numPro, $codSep, $date, $hour, $codUsu);

                if($closeOrders > 0) {
                    $createMov = $this->table->insertItemMov($codEmp, $codMov, $codPro, $uniMed, $qtdMov, $numLt, $codUsu, $date, $hour);

                    if($createMov) {
                        $return_picking = $this->picking($codEmp, $numPro, $codPro, $codSep, $date, $hour, $codUsu, $codTns, $codDer, $qtdMov);

                        if ($return_picking) {
                            return 'Processado com Sucesso!';

                        } else {
                            $this->table_picking->rollBackOrder($codSep);
                            return $return_picking;

                        }
                    } else {
                        $this->table_picking->rollbackOrder($codSep);
                        return 'Erro ao criar movimentação, contate o administrador do sistema!';
                    }

                } else {
                    return 'Erro ao finalizar OP, contate o administrador do sistema!';
                }
            }
        } else {
            return 'Centro de Custo informado errado!';
        }
    }

    public function picking($codEmp, $numPro, $codPro, $codSep, $date, $hour, $codUsu, $codTns, $codDer, $qtdMov)
    {
        /*
         * Válida as informações enviadas pelo Coletor
         */
        if ($codSep == NULL) {
            return 'Código de barras inválido!';
        }

        /*
         * Consulta se o Projeto informado na Ordem está cadastrado
         */
        $numPrj = $this->table_picking->consultProject($codEmp, $numPro);

        if (!$numPrj) {
            return 'Projeto não encontrado!';
        }

        /*
         * Consulta o Depósito de Origem do Projeto
         */
        $numDep = $numPrj->NUMPRJ;
        $depOri = $this->table_picking->consultDeposit($codEmp, $numDep);

        if (!$depOri) {
            return 'Depósito de Origem não encontrado!';
        }

        /*
         * Consulta o Depósito de Destino do Projeto
         */
        $numDepDes = $numDep . 'P';
        $depDes = $this->table_picking->consultDeposit($codEmp, $numDepDes);

        if (!$depDes) {
            return 'Depósito de Destino não encontrado!';
        }

        /*
         * Consulta Posição do estoque para Separação
         */
        $qtdEst = $this->table_picking->consultStock($codEmp, $codPro, $depOri->CODDEP, $qtdMov);

        if ($qtdEst) {
            /*
             * Consulta se existe ligação entre Produto x Depósito
             */
            $proDep = $this->table_picking->consultProdDep($codEmp, $codPro, $depDes->CODDEP);

            if (!$proDep) {
                /*
                 * Caso Falso, consulta a unidade de medida do produto
                 *  E realiza a ligação Produto x Depósito
                 */
                $uniMed = $this->table_picking->consultProdInfo($codEmp, $codPro);

                $this->table_picking->insertProductStock($codEmp, $codPro, $depDes->CODDEP, $date, $hour, $uniMed->UNIMED, $codUsu);
            }

            $pickingService = new WebServicePickingController();

            /*
             * Converte a Unidade de medida de (.) para (,)
             */
            $qtdPro = $this->functions->convertUnid($qtdMov);

            return $pickingService->execActionSid($codPro, $depOri, $qtdPro, $depDes, $codSep, $codUsu, $codTns, $codDer);

        } else {
            return 'Quantidade indisponível no estoque!';
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('picking::dispatch.index');
    }
}
