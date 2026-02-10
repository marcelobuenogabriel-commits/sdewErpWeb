<?php

namespace Modules\Producao\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Modules\Producao\App\Models\TKAEPDG;
use Modules\WebService\App\Http\Controllers\WebServiceController;

class OrdemProducaoController extends Controller
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
    public function show($id, $numorp = NULL)
    {
        if (empty($numorp)) {
            $ordembuscar = $id;
        } else {
            $ordembuscar = $numorp;
        }

        $resultWebService = $this->printOrderm($id, $ordembuscar);
        $resultListOrder = $resultWebService['resultOrdensList'];
        $resultListOrder = $resultListOrder->groupBy('usu_topsl');
        if ($resultWebService['return_web_service']['code'] == 200) {

            // Carrega o XML retornado pelo WebService
            $xmlString = $resultWebService['return_web_service']['file'];

            // Decodifica entidades HTML para XML real
            $xmlString = html_entity_decode($xmlString, ENT_QUOTES | ENT_XML1, 'UTF-8');

            // Usa DOMDocument para carregar o XML
            $dom = new \DOMDocument();
            libxml_use_internal_errors(true); // evita warnings

            if (!$dom->loadXML($xmlString)) {
                return response('Erro ao carregar o XML com DOMDocument.', 500);
            }

            // Busca o elemento <prRetorno>
            $elements = $dom->getElementsByTagName('prRetorno');

            if ($elements->length > 0) {
                $prRetorno = $elements->item(0)->nodeValue;

                if (!empty($prRetorno)) {
                    $pdfContent = base64_decode($prRetorno);

                    if (str_starts_with($pdfContent, '%PDF')) {
                        $pdfContent= $prRetorno;

                        return view('producao::printorder.show', compact('id', 'resultListOrder', 'pdfContent'));
                        #return response($pdfContent, 200)
                        #    ->header('Content-Type', 'application/pdf')
                        #    ->header('Content-Disposition', 'inline; filename="ordem_producao.pdf"');
                    } else {
                        return response('O conteúdo base64 não parece ser um PDF válido.', 400);
                    }
                } else {
                    return response('O campo prRetorno está vazio.', 400);
                }
            } else {
                return response('Elemento prRetorno não encontrado no XML.', 400);
            }
        } else {
            return response('Erro ao consultar o WebService.', 500);
        }

        #return view('printerOrder::show');
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

    public function printOrderm($numorp, $showorder)
    {
        $webService = new WebServiceController();
        $tkaepdg = new TKAEPDG();
        $resultordens = $tkaepdg->getOrdensMain($numorp);

        if (count($resultordens) > 0) {
            $min = $resultordens[0]->min_orp;
            $max = $resultordens[0]->max_orp;
        }

        $resultOrdensList = $tkaepdg->getOrdensList($min, $max);

        if (empty($numorp)) {
            session()->flash('error', 'Todos os campos são obrigatórios.');
            return \response()->json();
        }

        $return = $this->getOrdensPdf($showorder);
        $return_web_service = $this->convertStatusWebService($return['status_code'], $return['response']);

        return compact('return_web_service', 'resultOrdensList');
    }

    private function convertStatusWebService($status_code, $response)
    {
        $code_web_service = $status_code;
        $msg_web_service = $this->validXml($response);

        if ($code_web_service == 200 && empty($msg_web_service)) {
            $msg_return = [
                'code' => 200,
                'msg' => 'Impressão realizada com sucesso!',
                'file' => $response
            ];

        } else if ($code_web_service == 0) {
            $msg_return = [
                'code' => 500,
                'msg' => 'Problemas para se conectar com o servidor de impressão!'
            ];

        } else if ($code_web_service == 200 && $msg_web_service) {
            $msg_return = [
                'code' => 404,
                'msg' => $msg_web_service
            ];
        } else if ($code_web_service == 404) {
            $msg_return = [
                'code' => 404,
                'msg' => 'HTTP Status 404 - Not Found'
            ];
        }

        return $msg_return;
    }

    private function validXml($response)
    {
        $r = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
        $xml = new \SimpleXMLElement($r);

        if ($xml->body->h1) {
            return ['code' => 500, 'msg' => $xml->body->h1];
        }

        $msg = $xml->xpath('//erroExecucao')[0];
        return json_decode(json_encode((array)$msg), TRUE);
    }

    public function getOrdensPdf($strnumorp){

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://knbrglassfish01:8080/g5-senior-services/sapiens_Synccom_senior_g5_co_ger_relatorio?wsdl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_CONNECTTIMEOUT => 30, // tempo para conectar
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 600, // 10 Minutos
            CURLOPT_BUFFERSIZE => 128000, // aumenta o buffer
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "
              <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ser='http://services.senior.com.br'>
                <soapenv:Body>
                <ser:Executar>
                  <user>" . env('KNAPP_SAPIENS_USER') . "</user>
                  <password>" . env('KNAPP_SAPIENS_PWD') . "</password>
                  <encryption>" . env('KNAPP_SAPIENS_ENCRIPT') . "</encryption>
                  <parameters>
                    <prEntrada><![CDATA[<eNumOrp='" . $strnumorp . "'>]]></prEntrada>
                    <prEntranceIsXML>F</prEntranceIsXML>
                    <prExecFmt>tefFile</prExecFmt>
                    <prFileName>teste</prFileName>
                    <prRelatorio>DSPS200.GER</prRelatorio>
                    <prSaveFormat>tsfPDF</prSaveFormat>
                  </parameters>
                </ser:Executar>
                </soapenv:Body>
              </soapenv:Envelope>",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml'
            )
        ));

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        curl_close($curl);

        $status_code = (curl_getinfo($curl, CURLINFO_HTTP_CODE));

        return compact("response", "status_code");
    }
}
