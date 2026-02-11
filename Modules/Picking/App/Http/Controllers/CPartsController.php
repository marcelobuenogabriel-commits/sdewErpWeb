<?php

namespace Modules\Picking\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Picking\App\Models\PickingCParts;
use Modules\Picking\App\Models\PickingProducao;
use Modules\WebService\App\Http\Controllers\WebServicePickingController;

class CPartsController extends Controller
{
    protected $table;

    protected $date;

    protected $hour;

    protected $functions;

    public function __construct()
    {
        $this->middleware('auth');
        $this->table = new PickingCParts();
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
    public function store(Request $request)
    {
        $codUsu = auth()->user()->codusu;
        $codSep = $request->codSep;
        $ccuIni = $request->ccuIni;
        $codDer = '';

        if ($ccuIni == '7600') {
            /*
             * Consulta as Informações da tabela USU_TKAEPPO para separação
             */
            $info_order = $this->table->getQtdTotItem($codSep, $ccuIni);

            if (!$info_order) {
                $msg_return = 'Produto não localizado!';

            } else {
                $codEmp = $info_order->CODEMP;
                $numPro = $info_order->NUMPRO;
                $codPro = $info_order->CODPCK;
                $qtdMov = $info_order->QTDPCK;

                $date = $this->date;
                $hour = $this->functions->convertHours();
                $codTns = '90259';

                /*
                 * Finaliza as ordens referente ao Código de Separador informado
                 */
                $closeOrders = $this->table->closeOrder($codEmp, $numPro, $codSep, $codUsu, $date, $hour);

                if ($closeOrders > 0) {
                    $return_picking = $this->picking($codEmp, $qtdMov, $numPro, $codPro, $codSep, $date, $hour, $codUsu, $codTns, $codDer);

                    if ($return_picking) {
                        $msg_return = 'Processado com Sucesso!';

                    } else {
                        $this->table_picking->rollBackOrder($codSep);
                        $msg_return = $return_picking;

                    }
                } else {
                    $msg_return = 'Erro ao finalizar OP, contate o administrador do sistema!';
                }
            }
        } else {
            $msg_return = 'Centro de Custo informado errado!';
        }

        return \response()->json($msg_return);
    }

    protected function picking($codEmp, $qtdMov, $numPro, $codPro, $codSep, $date, $hour, $codUsu, $codTns, $codDer)
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
        return view('picking::cparts.index');
    }
}
