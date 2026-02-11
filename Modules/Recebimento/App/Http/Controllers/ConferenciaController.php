<?php

namespace Modules\Recebimento\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Recebimento\App\Models\Recebimento;
use Modules\WebService\App\Http\Controllers\WebServiceController;
use Illuminate\Support\Str;
use Modules\Recebimento\App\Events\InspecaoQualidadeEvent;
use Modules\FunctionCenter\App\Http\Controllers\FunctionCenterController;
use Illuminate\Notifications\Messages\MailMessage;

class ConferenciaController extends Controller
{

    protected $tableRecebimento;

    protected $webService;

    protected $date;

    public function __construct()
    {
        $this->middleware('auth');
        $this->tableRecebimento = new Recebimento();
        $this->webService = new WebServiceController();
        $this->date = date('Y-m-d');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $list_itens = $this->tableRecebimento->findItens();

        return view('recebimento::conferencia.index', compact('list_itens'),
            [
                'title' => 'Conferência de Nacional',
                'description' => 'Gerencie as conferências de mercadorias nacionais.'
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        if (!is_null($request->codPin)) {
            
            $emails = [
                'allysson.silva@knapp.com',
                'leandro.danilo@knapp.com',
                'bruno.carvalho@knapp.com'
            ];

            $send = new FunctionCenterController();

            $send->sendEmail(
                'Inspeção de Qualidade - ' . $request->codPro,
                $emails,
                $request->except([
                    'seqIpc',
                    'chvNel',
                    'codFor',
                    'codPin'
                ]),
                'functioncenter::emails.inspecaoQualidade',
                ''
            );
        }

        $uuid = Str::uuid();
        $qtdRec = (float) $request->qtdRec;
        $codPro = $request->codPro;
        $numNfc = $request->numNfc;
        $numOcp = $request->numOcp;
        $seqIpo = $request->seqIpo;
        $seqIpc = $request->seqIpc;
        $chvNel = $request->chvNel;
        $codPal = $request->codPal;
        $codFor = $request->codFor;

        // Remove espaços e caracteres não numéricos
        $cnpj = preg_replace('/\D/', '', $chvNel);

        // O CNPJ está entre as posições 7 e 20
        $cnpj = substr($cnpj, 6, 14);

        /*
         * Valida os parâmetros para inclusão do SKU
         */
        try {
            $return = $this->setPallet(
                $codPro,
                $numNfc,
                $numOcp,
                $seqIpo,
                $seqIpc,
                $chvNel,
                $qtdRec,
                $codPal,
                $codFor,
                $cnpj,
                $uuid
            );

            $code = $return['code'] == '200' ? 'success' : 'error';
            $msg = $return['msg'];
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Erro ao adicionar SKU: ' . $th->getMessage());
        }

        return redirect()->back()->with($code, $msg);
    }

    public function Pallet()
    {
        return view('recebimento::conferencia.pallet', [
            'title' => 'Impressão de Pallet',
            'description' => 'Gerencie a impressão de pallets.'
        ]);
    }

    /**
     * Set the pallet with the given parameters.
     *
     * @param string $codPro
     * @param string $numNfc
     * @param string $numOcp
     * @param int $seqIpo
     * @param int $seqIpc
     * @param string $chvNel
     * @param float $qtdRec
     * @param string $codPal
     * @param int|null $codFor
     * @return bool
     */
    public function setPallet($codPro, $numNfc, $numOcp, $seqIpo, $seqIpc, $chvNel, $qtdRec, $codPal, $codFor = NULL, $cnpj = NULL, $uuid)
    {
        $codUsu = \auth()->user()->codusu;

        /*
         * Adiciona o item na tabela USU_T_PALLET
         */
        try {
            $this->tableRecebimento->insertPallet(
                $this->date,
                $numOcp,
                $seqIpo,
                $codPal,
                $codPro,
                $qtdRec,
                $numNfc,
                $codUsu,
                $codFor,
                $cnpj,
                $uuid
            );
        } catch (\Throwable $th) {
            return ['code' => '404', 'msg' => 'Erro ao inserir o item no Pallet.'];
        }

        /*
         * Se o CNPJ for da KNAPP, chama a função de Importação
         * Caso contrário, chama a função de Nacional
         */
        if ($cnpj == '02322789000122') {
            return $this->setPalletImportacao($numOcp, $seqIpo, $qtdRec, $numNfc, $seqIpc, $codFor, $codPro, $uuid);
        } else {
            return $this->setPalletNacional($numOcp, $seqIpo, $qtdRec, $numNfc, $codFor, $chvNel, $seqIpc, $uuid);
        }

    }

    private function setPalletImportacao($numOcp, $seqIpo, $qtdRec, $numNfc, $seqIpc, $codFor, $codPro, $uuid) {

        try {
           $qtdXML = $this->tableRecebimento->findItemXMLImportacao($numNfc, $seqIpo, $numOcp, $codFor);

           /*
            * Se a unidade de medida for CT - Cento, é multiplicado por 100
            * Se a unidade de medida for ML - Milheiro, é multiplicado por 1000
            */
            if ($qtdXML[0]->uninfc == 'CT') {
                $qtdXML[0]->qtdrec = $qtdXML[0]->qtdrec * 100;
            } else if ($qtdXML[0]->uninfc == 'ML') {
                $qtdXML[0]->qtdrec = $qtdXML[0]->qtdrec * 1000;
            }

            $nQtdXML = (float) $qtdXML[0]->qtdrec;
        } catch (\Throwable $th) {
            return ['code' => '404', 'msg' => 'Erro ao consultar XML.'];
        }

        /*
         * Consulta a quantidade de itens informados na T_Pallet
         * Em casos onde o mesmo SKU é separado em mais de um pallet
         */
        try {
            $qtdPallet = $this->tableRecebimento->findPallet($numNfc, $seqIpo, $numOcp);

            $nQtdTPallet = (float) $qtdPallet[0]->QTDREC;
        } catch (\Throwable $th) {
            return ['code' => '404', 'msg' => 'Erro ao consultar Pallet.'];
        }

        /*
        *   Utiliza a função BCCOMP para comparar se as casas decimais são exatas.
        */
        if (bccomp($nQtdXML, $qtdRec, 5) == 0) {
            $this->tableRecebimento->closeIpcXMLImportacao($numOcp, $seqIpc, $numNfc, $codFor);
        } else if (bccomp($nQtdXML, $nQtdTPallet, 5) == 0) {
            $this->tableRecebimento->closeIpcXMLImportacao($numOcp, $seqIpc, $numNfc, $codFor);
        } else if ($qtdRec > $nQtdXML) {
            // Caso a quantidade informada seja maior que a quantidade do XML, não fecha o IPC
            $this->tableRecebimento->deletePallet($numNfc, $seqIpo, $numOcp, $qtdRec, $codPro);
            return ['code' => '400', 'msg' => 'Quantidade informada é maior que a quantidade do XML.'];
        } else if ($nQtdTPallet > $nQtdXML) {
            // Caso a quantidade do Pallet seja maior que a quantidade do XML, não fecha o IPC
            //$this->tableRecebimento->deletePallet($numNfc, $seqIpo, $numOcp, $qtdRec, $codPro);
            return ['code' => '400', 'msg' => 'Quantidade do Pallet é maior que a quantidade do XML.'];
        }

        return ['code' => '200', 'msg' => 'SKU adicionado com sucesso ao Pallet.'];
    }

    private function setPalletNacional($numOcp, $seqIpo, $qtdRec, $numNfc, $codFor, $chvNel, $seqIpc, $uuid) {
        /*
         * Fornecedores do sistema SAP, é realizado a divisão da sequência do Produto por 10
         * Atualizar a Regra 103 do sistema para atualizar na tabela E000IPC
         * 1021 - Siemens
         * 10091 - Siemens
         */
        if( $codFor == 10091 || $codFor == 1021) {

            $result = $seqIpo / 10;
            $resto = $seqIpo % 10;

            if($result >= 1 && $resto == 0) {
                $seqIpo = $seqIpo / 10;
            }
        }

        /*
         * Consulta a quantidade de itens por OC e Sequência no XML
         */
        try {
            $qtdXML = $this->tableRecebimento->findItemXML($numNfc, $seqIpo, $chvNel, $numOcp);

            /*
            * Se a unidade de medida for CT - Cento, é multiplicado por 100
            * Se a unidade de medida for ML - Milheiro, é multiplicado por 1000
            */
            if ($qtdXML[0]->uninfc == 'CT') {
                $qtdXML[0]->qtdrec = $qtdXML[0]->qtdrec * 100;
            } else if ($qtdXML[0]->uninfc == 'ML') {
                $qtdXML[0]->qtdrec = $qtdXML[0]->qtdrec * 1000;
            }

            $nQtdXML = (float) $qtdXML[0]->qtdrec;
        } catch (\Throwable $th) {
            return ['code' => '404', 'msg' => 'Erro ao consultar XML.'];
        }

        /*
         * Consulta a quantidade de itens informados na T_Pallet
         * Em casos onde o mesmo SKU é separado em mais de um pallet
         */
        try {
            $qtdPallet = $this->tableRecebimento->findPallet($numNfc, $seqIpo, $numOcp);

            $nQtdTPallet = (float) $qtdPallet[0]->QTDREC;
        } catch (\Throwable $th) {
            return ['code' => '404', 'msg' => 'Erro ao consultar Pallet.'];
        }

        /*
        *   Utiliza a função BCCOMP para comparar se as casas decimais são exatas.
        */
        if (bccomp($nQtdXML, $qtdRec, 5) == 0) {
            $this->tableRecebimento->closeIpcXML($numOcp, $seqIpc, $chvNel);
        } else if (bccomp($nQtdXML, $nQtdTPallet, 5) == 0) {
            $this->tableRecebimento->closeIpcXML($numOcp, $seqIpc, $chvNel);
        } else if ($nQtdXML < $nQtdTPallet) {

        }

        return ['code' => '200', 'msg' => 'SKU adicionado com sucesso ao Pallet.'];
    }

    public function createPallet(Request $request) {
        $numPrj = $request->txtNumPrj;
        $codFpj = $request->txtCodFpj;
        $tipPal = $request->txtTipPal;
        $oriReq = $request->txtOrigemReq;

        if ($oriReq == 'lista') {
            $locPrinter = 'PRBR004';
        } else {
            $locPrinter = 'PRBR001';
        }

        /*
        *   Valida os parâmetros para inclusão do Pallet
        */
        if (empty($numPrj) || empty($codFpj) || empty($tipPal)) {
            return redirect()->back()->with('error', 'Informações faltando, contate o administrador do sistema.');
        }

        /*
        *   Consulta se o Pallet já foi criado para o Projeto
        */
        $consultPalletNumber = $this->tableRecebimento->consultPalletNumber($numPrj, $tipPal);

        /*
        *   Se o Pallet ainda não foi criado, a tabela USU_TINDPAL é alimentada com os 4 tipos de pallets.
        *   Se não, é adicionado uma nova sequência e atualizado a linha do Pallet selecionado
        */
        if (empty($consultPalletNumber)) {
            $seqPal = $tipPal . '- 1';
        } else {
            $newSeqPallet = $consultPalletNumber->usu_indpal + 1;
            $seqPal = $tipPal . '-' . $newSeqPallet;
        }

        /*
        *   Envia via WebService para a Impressora PRBR001 a etiqueta do Pallet com a nova sequência
        */
        $printerPallet = $this->webService->printerPallet($numPrj, $codFpj, $seqPal, $locPrinter);

        if ($printerPallet['code'] != 200) {
            return redirect()->back()->with('error', 'Erro ao imprimir o Pallet: ' . $printerPallet['msg']);
        }

        /* TODO melhorar a lógica de criação do Pallet
            ou fazer um rollback caso ocorra algum erro
            ou melhorar a lógica de criação do Pallet
        */
        if (empty($consultPalletNumber)) {
            $this->tableRecebimento->createPalle($numPrj, $tipPal);
        } else {
            $this->tableRecebimento->updatePallet($numPrj, $tipPal, $newSeqPallet);
        }

        return redirect()->back()->with('success', $printerPallet['msg']);
    }

    public function printTag(Request $request)
    {
        $numOcp = $request->numOcp;
        $seqIpo = $request->seqIpo;
        $qtdImp = $request->qtdImp;
        $qtdEti = $request->qtdEti;

        $returnWebService = $this->webService->printerTagOcp($numOcp, $seqIpo, $qtdImp, $qtdEti);

        return \response()->json($returnWebService);
    }
}
