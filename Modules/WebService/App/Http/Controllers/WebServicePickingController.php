<?php

namespace Modules\WebService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\WebService\App\Facades\SeniorSoap;

class WebServicePickingController extends Controller
{
    private $user;

    private $pw;

    private $cript;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = 'sapienspa';
        $this->pw = 'S4p13nsp4';
        $this->cript = 0;
    }

    public function execActionSid($codPro, $depOri, $qtdMov, $depDes, $codSep, $codUsu, $codTns, $codDer)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://knbrglassfish01:8080/g5-senior-services/sapiens_Synccom_senior_g5_co_mcm_est_estoques?wsdl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>"
		    <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ser='http://services.senior.com.br'>
			  <soapenv:Body>
				<ser:MovimentarEstoque>
				  <user>$this->userErp</user>
				  <password>$this->pwErp</password>
				  <encryption>$this->encryp</encryption>
				  <parameters>
					<dadosGerais>
					  <codEmp>1</codEmp>
					  <codFil>1</codFil>
					  <codPro>$codPro</codPro>
					  <codDer>".$codDer."</codDer>
					  <codDep>$depOri->CODDEP</codDep>
					  <qtdMov>$qtdMov</qtdMov>
					  <ctaFin>0</ctaFin>
					  <codTns>$codTns</codTns>
					  <usuRes>$codUsu</usuRes>
					  <usuRec>$codUsu</usuRec>
					  <proTrf>$codPro</proTrf>
					  <derTrf>".$codDer."</derTrf>
					  <depTrf>$depDes->CODDEP</depTrf>
					  <motMvp>$codSep</motMvp>
					</dadosGerais>
				  </parameters>
				</ser:MovimentarEstoque>
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

        return $this->convertStatusWebService($status_code, $response);
    }

    /**
     * Example usage of Senior SOAP client to consult user by identifier.
     */
    public function consultarUsuario(Request $request)
    {
        $identifier = $request->get('cpf') ?? $request->get('id');

        try {
            $response = SeniorSoap::consultarUsuario(['identifier' => $identifier]);
            return response()->json(['code' => 200, 'data' => $response]);
        } catch (\Throwable $e) {
            \Log::error('Senior SOAP consult error', ['error' => $e->getMessage()]);
            return response()->json(['code' => 500, 'message' => 'Erro ao consultar usuário: '.$e->getMessage()]);
        }
    }

    private function convertStatusWebService($status_code, $response)
    {
        $code_web_service = $status_code;
        $msg_web_service = $this->validXml($response);

        if ($code_web_service == 200 && empty($msg_web_service)) {
            $msg_return = [
                'code' => 200,
                'msg' => 'Impressão realizada com sucesso!'
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
        }

        return $msg_return;
    }

    private function validXml($response)
    {
        if ($response) {
            $r = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
            $xml = new \SimpleXMLElement($r);

            $msg = $xml->xpath('//erroExecucao')[0];
            return json_decode(json_encode((array)$msg), TRUE);
        }
    }
}
