<?php

namespace Modules\Picking\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Picking\App\Models\PickingDispatch;
use Modules\Picking\App\Models\PickingProducao;
use Modules\WebService\App\Http\Controllers\WebServicePickingController;

class PickingController extends Controller
{
    protected $table;

    protected $date;

    protected $hour;

    public function __construct()
    {
        $this->middleware('auth');
        $this->table = new PickingProducao();
        $this->date = date('Y-m-d');
        $this->hour = date('h:i');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('picking::producao.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $codUsu = auth()->user()->codusu;
        $codSep = $request->codSep;
        $datSep = $request->datSep;
        $ccuIni = $request->ccuIni;
        $codDer = '';

        if ($ccuIni == '4460') {
            /*
             * Consulta as Informações da tabela USU_TKAEPPO para separação
             */
            $info_order = $this->table->getQtdTotItem($codSep, $ccuIni);

            if (!$info_order) {
                $msg_return = 'Produto não localizado!';

            } else {

                $codEmp = $info_order->CODEMP;
                $numPro = $info_order->NUMPRO;
                $qtdMov = $info_order->QTDPCK;
                $iniPro = $info_order->INIPRO;
                $codPro = $info_order->CODPCK;
                $date = $this->date;
                $hour = $this->convertHours();
                $codTns = '90259';

                /*
                 * Finaliza as ordens referente ao Código de Separador informado
                 */
                $this->table->closeOrder($codEmp, $numPro, $codSep, $codUsu, $date, $hour);

                $return_picking = $this->picking($codEmp, $datSep, $qtdMov, $iniPro, $numPro, $codPro, $codSep, $date, $hour, $codUsu, $codTns, $codDer);

                if ($return_picking) {
                    $msg_return = 'Processado com Sucesso!';

                } else {
                    $this->table->rollBackOrder($codSep);
                    $msg_return = $return_picking;

                }
            }
        } else {
            $msg_return = 'Centro de Custo informado errado!';
        }

        return \response()->json($msg_return);
    }

    public function picking($codEmp, $datSep, $qtdMov, $iniPro, $numPro, $codPro, $codSep, $date, $hour, $codUsu, $codTns, $codDer)
    {
        /*
         * Válida as informações enviadas pelo Coletor
         */
        if ($codSep == NULL || $datSep == NULL) {
            return 'Código de barras inválido!';
        }

        /*
         * Formata data de Inicio do Picking
         *  Para comparar com a data informada via Coletor
         */
        $iniProFormated = date('d/m/Y', strtotime($iniPro));

        if ($iniProFormated <> $datSep) {
            return 'Localização de destino diferente do Produto!';
        }

        /*
         * Consulta se o Projeto informado na Ordem está cadastrado
         */
        $numPrj = $this->table->consultProject($codEmp, $numPro);

        if (!$numPrj) {
            return 'Projeto não encontrado!';
        }

        /*
         * Consulta o Depósito de Origem do Projeto
         */
        $numDep = $numPrj->NUMPRJ;
        $depOri = $this->table->consultDeposit($codEmp, $numDep);

        if (!$depOri) {
            return 'Depósito de Origem não encontrado!';
        }

        /*
         * Consulta o Depósito de Destino do Projeto
         */
        $numDepDes = $numDep . 'P';
        $depDes = $this->table->consultDeposit($codEmp, $numDepDes);

        if (!$depDes) {
            return 'Depósito de Destino não encontrado!';
        }

        /*
         * Consulta Posição do estoque para Separação
         */
        $qtdEst = $this->table->consultStock($codEmp, $codPro, $depOri->CODDEP, $qtdMov);

        if ($qtdEst) {

            /*
             * Consulta se existe ligação entre Produto x Depósito
             */
            $proDep = $this->table->consultProdDep($codEmp, $codPro, $depDes->CODDEP);

            if (!$proDep) {
                /*
                 * Caso Falso, consulta a unidade de medida do produto
                 *  E realiza a ligação Produto x Depósito
                 */
                $uniMed = $this->table->consultProdInfo($codEmp, $codPro);

                $this->table->insertProductStock($codEmp, $codPro, $depDes->CODDEP, $date, $hour, $uniMed->UNIMED, $codUsu);
            }

            $pickingService = new WebServicePickingController();

            /*
             * Converte a Unidade de medida de (.) para (,)
             */
            $qtdPro = $this->convertUnid($qtdMov);
            $obsMov = 'Movimentação oriunda do separador: ' . $codSep;

            return $pickingService->execActionSid($codPro, $depOri, $qtdPro, $depDes, $codSep, $codUsu, $codTns, $codDer);
        } else {
            return 'Quantidade indisponível em estoque.';
        }
    }

    protected function convertUnid($qtdMov)
    {
        $qtdMovFloat = (float)$qtdMov;
        return str_replace('.', ',', $qtdMovFloat);
    }

    public function convertHours()
    {
        $resultHora = explode(':', $this->hour);
        $horas = $resultHora[0] * 60;
        $minutos = $resultHora[1];

        return $hour = $horas + $minutos;
    }
}
