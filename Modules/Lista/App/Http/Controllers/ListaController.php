<?php

namespace Modules\Lista\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\FunctionCenter\App\Emails\Mail;
use Modules\FunctionCenter\App\Http\Controllers\FunctionCenterController;
use Modules\Lista\App\Http\Requests\ListaRequest;
use Modules\Lista\App\Models\Lista;
use Modules\WebService\App\Http\Controllers\WebServiceController;

class ListaController extends Controller
{

    private $table;
    protected $web_service;

    protected $user;

    protected $to;

    protected $content;

    public function __construct()
    {
        $this->middleware('auth');
        $this->table = new Lista();
        $this->web_service = new WebServiceController();
        $this->to = [
            'allysson.silva@knapp.com',
            'marcelo.gabriel@knapp.com'
            //'leandro.danilo@knapp.com',
            //'christian.novac@knapp.com',
            //'diego.santos@knapp.com',
            //'vinicius.vulliano@knapp.com'
        ];

        $this->content = 'functioncenter::emails.sendEmailLista';
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $listas = $this->table->findAll();
        return view('lista::index', compact('listas'));
    }

    public function showItensLista(ListaRequest $request)
    {
        $seqPro = $request->seqPro;
        $codPlt = $request->codPlt;

        /*
         * Verifica se o livro está em 999 (Se sim irá realizar uma veiricação e alteração de agrupamento de livro de
         * acordo com a ordenação da posição de estoque. Sendo assim quando o Novac realiza a importação o sistema insere
         * 999 no campo usu_numliv e aqui é realizado o ajuste para a tela de páginas.
         */
        $count_lista = $this->table->checkLista($seqPro);

        if ($count_lista >= 1) {
            $aux = 0;
            $numLvr = 0;

            $count_itens = $this->table->getIntesLista($seqPro);

            foreach ($count_itens as $item) {
                if (($aux % 20) == 0)
                    $numLvr = $numLvr + 1;

                $aux++;
                $this->table->updateTsupIte($seqPro, $item->USU_CODPRO, $item->USU_NUMIND, $numLvr);
            }
        }

        $itens_lista = $this->table->getIntesLista($seqPro);

        return view('lista::showitenslista', compact('itens_lista', 'codPlt', 'seqPro'));
    }

    public function showItensLiv(Request $request)
    {
        $seqPro = $request->seqPro;
        $codPlt = $request->codPlt;
        $numLiv = $request->numLiv;

        $itens_lista = $this->table->getIntesListaLivro($seqPro, $numLiv);

        return view('lista::showitensliv', compact('itens_lista', 'seqPro', 'codPlt'));
    }

    public function setQtdaltante(Request $request)
    {
        $codUsu = auth()->user()->codusu;
        $now = date('Y-m-d');

        $seqPro = $request->seqPro;
        $codPro = $request->codPro;
        $qtdFal = $request->qtdFal;
        $codEnd = $request->codEnd;
        $codTns = '90298';
        $codPlt = strtoupper($request->codPlt);

        /*
         * Consitência da sequência de procedimento
         */
        $return_seq = $this->table->consistSeqProc($seqPro);

        if (!$return_seq) {
            session()->flash('error', 'Sequência de Produto não encontrada.');
            return true;
        }

        $numPro = $return_seq->usu_numprj;
        $depDes = $return_seq->usu_depdes;

        /*
         * Consiste Produto
         */
        $return_prod = $this->table->consistProd($seqPro, $codPro);

        if (!$return_prod) {
            session()->flash('error', 'Produto não localizado ou não transferido.');
            return true;
        }

        if ($return_prod->usu_qtdmov == 0) {
            session()->flash('error', 'Produto inválido ou inexistente na lista de separação.');
            return true;
        }

        $qtd_prod_lista = $return_prod->usu_qtdmov;

        /*
         * Consiste USU_TRECPROC
         */
        $return_trec = $this->table->consistTrecProc($seqPro, $codPro);

        if ($return_trec->count() > 0) {
            $this->table->updateTrecProc($seqPro, $codPro, $now);
        } else {
            $this->table->insertTrecProc($seqPro, $codPro, $now);
        }

        $qtdMov = $qtd_prod_lista - $qtdFal;

        if ($qtdMov > 0) {
            $this->table->updateTrecProcInfo($qtdMov, $numPro, $depDes, $seqPro, $codPro, $codPlt);

            /*
            *   Muda o depósito de Origem DepTra para I sendo este necessário para realizar a transferência
            *    para o depósito de destino sem o I
            */
            $depOri = $depDes . 'I';

            /*
            *   Verifica disponibilidade em estoque para o item
            */
            $has_stock = $this->table->getDispEstoque($codPro, '', $depOri, $qtdMov);

            if ($has_stock && $depDes <> '111' && $depDes <> '113' && $depDes <> '219') {

                /*
                *   Verifica se o item já está ligado ao depósito de destino.
                */
                $has_connection = $this->table->getLigDeposito($codPro, '', $depDes);

                if (!$has_connection) {
                    /*
                    *   Caso o item não esteja ligado ao depósito, busca a unidade de medida e insere a ligação.
                    */
                    $uniMed = $this->table->getUnidadeMedida($codPro);

                    $this->table->insertLigProduto($codPro, '', $depDes, $uniMed[0]->unimed, $now, $codUsu);
                }

                $obsMov = 'Movimento oriundo da Lista' . $seqPro;

                $return_web_service = $this->web_service->movEstoque($codPro, '', $depOri, $qtdMov, $depDes, '', $codUsu, $obsMov, $codTns);

                if ($return_web_service->code == '200') {
                    $this->table->closeItensLista($seqPro, $codPro, $codUsu);

                    return $return_web_service;
                } else {
                    $this->table->rollbackTrecProc($seqPro, $codPro);

                    session()->flash('error', 'Processo cancelado.');
                    return true;
                }
            } else {
                $this->table->closeItensLista($seqPro, $codPro, $codUsu);
                session()->flash('success', 'Processado com sucesso.');
                return true;
            }

        } else {
            $this->table->setMovZerado($seqPro, $codPro);
            session()->flash('error', 'Nenhum produto separado, quantidade zerada.');
            return true;
        }
    }

    public function cancelaLista(Request $request) 
    {
        $seqPro = $request->seqPro;

        $this->table->cancelLista($seqPro);

        return redirect()->route('lista.index')
            ->with('success', 'Lista cancelada com sucesso.');
    }

    public function finalizaLista(Request $request)
    {
        $listas = $this->table->findAll();
        $seqPro = $request->seqPro;

        /*
         * Consitência da sequência de procedimento
         */
        $return_seq = $this->table->consistSeqProc($seqPro);

        if (!$return_seq) {
            return redirect()->route('lista.index')
                ->with('error', 'Sequência de Produto não encontrada.');
        }

        /*
        * Consistência Produto
        */
        $return_prod = $this->table->consistTrecProcLista($seqPro);

        if ($return_prod->count() == 0) {
            return redirect()->route('lista.index', compact('listas'))
                ->with('error', 'Não foram encontrados itens para o encerramento da lista.');
        }

        /*
        * Consiste Produtos da USU_TRECPROC
        */
        $return_trec = $this->table->consistTrecProcQtd($seqPro);

        if ($return_trec->count() > 0) {
            $this->table->updateTsupDgr($seqPro);


            return redirect()->route('lista.index', compact('listas'))
                ->with('error', 'Existem itens com quantidade faltante!');
        } else {
            $consit_final = $this->table->consistTrecProcFinal($seqPro);

            if ($consit_final->count() > 0) {
                $this->table->updateTrecProcZero($seqPro);

                return redirect()->route('lista.index', compact('listas'))
                    ->with('error', 'Existem itens com quantidade Faltante.');
            } else {
                $this->table->updateTsupDgr($seqPro);

                $send_email = new FunctionCenterController();

                $body = $seqPro;
                $subject = 'Lista de Separação Encerrada '.$seqPro;

                $send_email->sendEmail($subject, $this->to, $body, $this->content, null);

                $this->gerarCompra($seqPro);

                return redirect()->route('lista.index', compact('listas'))->with('success', 'E-mail enviado com sucesso!');
            }
        }
    }

    public function gerarCompra($seqPro)
    {
        $count = 1;
        $body = '';
        $codUsu = auth()->user()->codusu;

        $lista = $this->table->consistSeqProc($seqPro);

        $numPrj = $lista->usu_numprj;
        $depDes = $lista->usu_depdes;

        $body = "<p>Necessidade de Compra - Projeto <b>$numPrj</b></p>
                        <p>Seq. Procedimento: $seqPro <br/></p>
                          <table border=\"1\" cellpadding=\"1\" cellspacing=\"1\" style=\"height:61px; width:465px\">
                            <tbody>
                            <tr>
                                <td>Produto</td>
                                <td>Quantidade</td>
                            </tr> ";

        $lista_faltante = $this->table->consistTrecProcQtd($seqPro);

        foreach ($lista_faltante as $item) {
            $codPro = $item['usu_codpro'];
            $qtdOri = $item['usu_qtdori'];
            $qtdMov = $item['usu_qtdmov'];
            $qtdSol = $qtdOri - $qtdMov;

            if($count <> 0) {

                $now = date('Y-m-d');

                $horIni = date('h:i');

                $resultHora = explode(':', $horIni);
                $horas = $resultHora[0] * 60;
                $minutos = $resultHora[1];

                $horSis = $horas + $minutos;

                /*
                 * Retorna o último registro da tabela USU_TESTDGR
                 */

                $last_regist = $this->table->getLastRegistDGR();
                $new_regist = $last_regist + 1;

                /*
                 * Adiciona cabeçalho da USU_TESTDGR
                 */
                $this->table->addNewHeaderDG($new_regist, $codUsu, $now, $horSis);
            }

            /*
             * Consulta a Conta Financeira referente a família em que o produto está cadastrado
             */
            $ctaFin = $this->table->getContaFinanceira($codPro);

            if($ctaFin->count() == 0) {
                return false;
            }

            /*
             * Válida novo processo de transferência entre depósitos
             */
            if ($seqPro >= 3188) {
                if ($depDes <> '111' && $depDes <> '113' && $depDes <> '219') {
                    $depOri = $depDes . 'I';
                } else {
                    $depOri = $depDes;
                }

                $qtdPro = $this->table->getQuantidadeProd($codPro, $depOri);
            } else {
                /*
                 * Retorna a quantidade de produto no Estoque do Projeto
                 */
                $qtdPro = $this->table->getQuantidadeProd($codPro, $depDes);
            }

            if(!$qtdPro) {
                return false;
            }

            $qtdNut = $qtdPro['QTDEST'] - $qtdSol;

            $this->table->addNewItemDG($new_regist, $codPro, $qtdPro['QTDEST'], $qtdNut, $ctaFin, $codUsu, $now, $horSis);

            $body = $body . " <tr>
                                            <td>$codPro</td>
                                            <td>$qtdNut</td>
                                        </tr>";

            $nCount = 0;

        }

        if($count == 0) {
            $body = $body . "</tbody> </table>";

            $send_email = new FunctionCenterController();
            $send_email->sendEmail('', $this->to, $body, $this->content, null);
        }

        $this->table->finalizaTrecProc($seqPro);

        return true;
    }
}
