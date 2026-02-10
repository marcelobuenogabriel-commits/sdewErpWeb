<?php
namespace Modules\WebService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\WebService\App\Services\SeniorSoapService;

class WebServicePreFaturaController extends Controller
{

    protected $soap_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->soap_service = new SeniorSoapService();
    }
    
    public function etiqueta(array $filters = [], array $options = [])
    {

        $body = $this->soap_service->buildSoapEnvelope(
                'Executar',
                [],
                [
                    'prCallMode' => 1,
                    'FlowName' => '',
                    'FlowInstanceID' => '',
                    'prPrintDest' => '\\\\KNBRPR01\\PRBR010',
                    'prExecFmt' => 'tefPrint',
                    'prRelatorio' => 'RFEX103.GER',
                    'prEntranceIsXML' => 'F',
                    'prEntrada' => ''   
                ],
                '',
                [],
                '',
                []
        ); 

        $return = $this->soap_service->callWithHttp(
            url: 'http://knbrglassfish01:8080/g5-senior-services/sapiens_Synccom_senior_g5_co_ger_relatorio',
            body: $body,
            headers: [],
            options: []
        );

        if (!empty($return['Body']['ExecutarResponse']['result']['erroExecucao'])) {
            $error = $return['Body']['ExecutarResponse']['result']['erroExecucao'];
            return response()->json(['error' => $error], 500);
        } 
        
        return response()->json($return, 200);
    }

    public function gerarEmbalagemPreFatura(array $filters = [], array $options = [])
    {

        $x = 1;
        $array_produtos = [];

        foreach ($options['result'] as $produto) {
            
            if ($x == 1) {
                $pesbru = $options['pesbru'];
                $pesliq = $options['pesliq'];
            } else {
                $pesbru = 0;
                $pesliq = 0;
            }
            
            $array_produtos[] = [
                'codEmp' => 1,
                'codFil' => 1,
                'numAne' => $options['numane'],
                'numPfa' => $options['numpfa'],
                'seqEmb' => 1,
                'seqEpd' => $x++,
                'seqPes' => $produto->seqpes,
                'qtdPro' => $options['qtdpfa'] ? $options['qtdpfa'] : $produto->qtdppf - $produto->qtdpro,
                'pesbru' => $pesbru,
                'pesliq' => $pesliq
            ];
        }
      
        $body = $this->soap_service->buildSoapEnvelope(
                'Gerar',
                [],
                [
                    'codEmp' => 1,
                    'codFil' => 1,
                    'numAne' => $options['numane'],
                    'numPfa' => $options['numpfa'],
                    'finalizarPreFatura' => 'N',
                    'permiteSeqNaoEmbalada' => 'S',
                    'permiteDiferencaQuantidades' => 'S',
                    'excluirEmbalagensExistentes' => 'N',
                    'qtdPpfIgualQtdEmbalar' => 'N'
                ], 
                'embalagens',
                [
                    'seqEmb' => '1',
                    'codEmb' => $options['codemb'],
                    'qtdEmb' => 1,
                    'numEmb' => '1',
                    'numNiv' => 1,
                    'volOcu' => '0',
                    'pesBru' => $options['pesbru'],
                    'pesLiq' => $options['pesliq'],
                    'obsEmb' => $options['obsemb'],
                    'sitEmb' => 1
                ],
                'itensEmb',
                $array_produtos
        );
        
        $return = $this->soap_service->callWithHttp(
            url: 'http://knbrglassfish01:8080/g5-senior-services/sapiens_Synccom_senior_g5_co_mcm_ven_embalagempfa',
            body: $body,
            headers: [],
            options: []
        );

        if (!empty($return['Body']['GerarResponse']['result']['erroExecucao'])) {
            $error = $return['Body']['GerarResponse']['result']['erroExecucao'];
            return response()->json(['error' => $error], 401);
        } 
        
        if (!empty($return['Body']['GerarResponse']['result']['retorno'])) {
            $message = $return['Body']['GerarResponse']['result']['retorno'];
            return response()->json(['message' => $message], 400);
        }

        return response()->json($return, 200);
    }
}