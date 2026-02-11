<?php

namespace Modules\WebService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\WebService\App\Services\SeniorSoapService;

class WebServiceController extends Controller
{

    private $user;

    private $pw;

    private $cript;

    protected $soap_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = 'sapienspa';
        $this->pw = 'S4p13nsp4';
        $this->cript = 0;
        $this->soap_service = new SeniorSoapService();
    }

    public function consultarUsuario(array $filters = [], array $options = [])
    {
      $servico = 'sapiens_Synccom_senior_g5_co_ger_cad_usuario?wsdl';
      return $this->soap_service->call('exportarAbrangencia', $filters, $options);
    }

    public function printerPallet($numPrj, $codFpj, $seqPal, $locPrinter)
    {
      $curl = curl_init();

      curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://knbrglassfish01:8080/g5-senior-services/sapiens_Synccom_senior_g5_co_ger_relatorio?wsdl',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => "
              <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ser='http://services.senior.com.br'>
                <soapenv:Body>
                <ser:Executar>
                  <user>" . $this->user . "</user>
                  <password>" . $this->pw . "</password>
                  <encryption>" . $this->cript . "</encryption>
                  <parameters>					
                            <prPrintDest>\\\\knbrpr01\\prbr001</prPrintDest>
                            <prExecFmt>tefPrint</prExecFmt>
                            <prRelatorio>SRNF120.GER</prRelatorio>                    
                            <prEntrada><![CDATA[<ECodEmp=1><ECodFil=1><ENumFor=" . $numPrj . "><ENumFas=" . $codFpj . "><EQtdPct=" . $seqPal . ">]]></prEntrada>
                            <prEntranceIsXML>F</prEntranceIsXML>
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

      return $this->convertStatusWebService($status_code, $response);
    }

    public function printerTagOcp($numOcp, $seqIpo, $qtdImp, $qtdEti)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://knbrglassfish01:8080/g5-senior-services/sapiens_Synccom_senior_g5_co_ger_relatorio?wsdl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "
              <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ser='http://services.senior.com.br'>
                <soapenv:Body>
                <ser:Executar>
                  <user>" . $this->user . "</user>
                  <password>" . $this->pw . "</password>
                  <encryption>" . $this->cript . "</encryption>
                  <parameters>
                            <prPrintDest>\\\\knbrpr01\\prbr006</prPrintDest>
                            <prExecFmt>tefPrint</prExecFmt>
                            <prRelatorio>SRNF220.GER</prRelatorio>
                            <prEntrada><![CDATA[<ECodEmp=1><ECodFil=1><ENumOcp=" . $numOcp . "><eSeqIpo=" . $seqIpo . "><eQtdImp=" . number_format($qtdEti, 2) . ">]]></prEntrada>
                            <prEntranceIsXML>F</prEntranceIsXML>
                  </parameters>
                </ser:Executar>
                </soapenv:Body>
              </soapenv:Envelope>",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/xml'
            )
        ));

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        for ($i = 0; $i < $qtdImp; $i++) {
            $response = curl_exec($curl);
        }

        curl_close($curl);

        $status_code = (curl_getinfo($curl, CURLINFO_HTTP_CODE));

        return $this->convertStatusWebService($status_code, $response);
    }

    public function printerTag($value)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://knbrglassfish01:8080/g5-senior-services/sapiens_Synccom_senior_g5_co_ger_relatorio?wsdl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "
		    <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ser='http://services.senior.com.br'>
			  <soapenv:Body>
				<ser:Executar>
				  <user>" . $this->user . "</user>
				  <password>" . $this->pw . "</password>
				  <encryption>" . $this->cript . "</encryption>
				  <parameters>
                    <prPrintDest>\\\\knbrpr01\\prbr006</prPrintDest>
                    <prExecFmt>tefPrint</prExecFmt>
                    <prRelatorio>SRNF212.GER</prRelatorio>
                    <prEntrada><![CDATA[<ECodEmp=1><ECodFil=1><ENumOcp=" . $value->numocp . "><ESeqIpo=" . $value->seqipo . "><EQtdImp=1>]]></prEntrada>
                    <prEntranceIsXML>F</prEntranceIsXML>
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
        
        return $this->convertStatusWebService($status_code, $response);
    }

    public function printReport($parans)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://knbrglassfish01:8080/g5-senior-services/sapiens_Synccom_senior_g5_co_ger_relatorio?wsdl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "
              <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ser='http://services.senior.com.br'>
                <soapenv:Body>
                <ser:Executar>
                  <user>" . $this->user . "</user>
                  <password>" . $this->pw . "</password>
                  <encryption>" . $this->cript . "</encryption>
                  <parameters>
                            <prCallMode>1</prCallMode>
                            <FlowName></FlowName>
                            <FlowInstanceID></FlowInstanceID>
                            <prPrintDest>" . $parans['prPrintDest'] . "</prPrintDest>
                            <prExecFmt>" . $parans['prExecFmt'] . "</prExecFmt>
                            <prRelatorio>" . $parans['prRelatorio'] . "</prRelatorio>
                            <prEntrada>" . $parans['prEntrada'] . "</prEntrada>
                            <prEntranceIsXML>" . $parans['prEntranceIsXML'] . "</prEntranceIsXML>
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

        $return_web_service = $this->convertStatusWebService($status_code, $response);

        return $this->returnResponse($return_web_service);
    }

    /*
     * Emitir o relatório e enviar via E-mail
     */
    public function printReportByEmail($parans)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://knbrglassfish01:8080/g5-senior-services/sapiens_Synccom_senior_g5_co_ger_relatorio?wsdl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "
            <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ser='http://services.senior.com.br'>
              <soapenv:Body>
              <ser:Executar>
                <user>" . $this->user . "</user>
                <password>" . $this->pw . "</password>
                <encryption>" . $this->cript . "</encryption>
                <parameters>
                          <prCallMode>1</prCallMode>
                          <FlowName></FlowName>
                          <FlowInstanceID></FlowInstanceID>
                          <prRemetente>" . $parans['prRemetente'] . "</prRemetente>
                          <prFileName>" . $parans['prFileName'] . "</prFileName>
                          <prDest>" . $parans['prDest'] . "</prDest>
                          <prAssunto>" . $parans['prAssunto'] . "</prAssunto>
                          <prAnexoBool>" . $parans['prAnexoBool'] . "</prAnexoBool>
                          <prSaveFormat>" . $parans['prSaveFormat'] . "</prSaveFormat>
                          <prExecFmt>" . $parans['prExecFmt'] . "</prExecFmt>
                          <prRelatorio>" . $parans['prRelatorio'] . "</prRelatorio>
                          <prEntrada>" . $parans['prEntrada'] . "</prEntrada>
                          <prEntranceIsXML>" . $parans['prEntranceIsXML'] . "</prEntranceIsXML>
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

        $return_web_service = $this->convertStatusWebService($status_code, $response);

        return $this->returnResponse($return_web_service);
    }

    public function contageInvent($parans)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://knbrglassfish01:8080/g5-senior-services/sapiens_Synccom_senior_g5_co_mcm_est_contageminventario?wsdl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '
				  <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://services.senior.com.br">
					  <soapenv:Body>
						<ser:ContagemInventarioEstoque>
						  <user>' . $this->user . '</user>
						  <password>' . $this->pw . '</password>
						  <encryption>' . $this->cript . '</encryption>
						  <parameters>
							<dadosGerais>
							  <codEmp>' . $parans['codEmp'] . '</codEmp>
							  <datInv>' . $parans['datInv'] . '</datInv>
							  <codDep>' . $parans['codDep'] . '</codDep>
							  <numCon>' . $parans['numCon'] . '</numCon>
							  <tipInv>' . $parans['tipInv'] . '</tipInv>
							  <numDoc>' . $parans['numDoc'] . '</numDoc>
							  <codMod>' . $parans['codMod'] . '</codMod>
							  <codPro>' . $parans['codPro'] . '</codPro>
							  <codDer>' . $parans['codDer'] . '</codDer>
							  <qtdCon>' . $parans['qtdCon'] . '</qtdCon>
							</dadosGerais>
						  </parameters>
						</ser:ContagemInventarioEstoque>
					  </soapenv:Body>
					</soapenv:Envelope>',
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

    public function movEstoque($codPro, $codDer, $depOri, $qtdMov, $depDes, $codSep, $codUsu, $obsMov, $codTns)
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
            CURLOPT_POSTFIELDS => "
		  <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ser='http://services.senior.com.br'>
			  <soapenv:Body>
				<ser:MovimentarEstoque>
				  <user>$this->user</user>
				  <password>$this->pw</password>
				  <encryption>$this->cript</encryption>
				  <parameters>
					<dadosGerais>
					  <codEmp>1</codEmp>
					  <codFil>1</codFil>
					  <codPro>$codPro</codPro>
					  <codDer>" . $codDer . "</codDer>
					  <codDep>$depOri</codDep>
					  <qtdMov>$qtdMov</qtdMov>
					  <ctaFin>0</ctaFin>
					  <codTns>$codTns</codTns>
					  <usuRes>$codUsu</usuRes>
					  <usuRec>$codUsu</usuRec>
					  <proTrf>$codPro</proTrf>
					  <derTrf>" . $codDer . "</derTrf>
					  <depTrf>$depDes</depTrf>
					  <motMvp>'.$codSep.'</motMvp>
					  <motMvp>$obsMov</motMvp>
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

    public function uploadnfexml($nomearquivo, $xml)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://knbrerp03:8989/SDE/Upload?wsdl',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "
		  <soapenv:Envelope xmlns:soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/' xmlns:nfe='http://www.senior.com.br/nfe'>
			  <soapenv:Body>
				<ser:UploadArquivo>
				  <user>admin</user>
				  <password>adm</password>
				  <nfe:tipoDocumento>1</nfe:tipoDocumento>
				  <nfe:nomeArquivo>teste.xml</nfe:nomeArquivo>
				  <nfe:arquivo>$xml</nfe:arquivo>
				</ser:UploadArquivo>
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

    public function returnResponse($return_web_service)
    {
        if ($return_web_service['code'] == 200) {
            session()->flash('success', $return_web_service['msg']);
        } else {
            session()->flash('error', $return_web_service['msg'][0]);
        }

        return \response()->json($return_web_service);
    }

    function parseSOAPResponse($response) {
      try {
          $dom = new DOMDocument();
          $dom->preserveWhiteSpace = false;
          $dom->formatOutput = true;
          $dom->loadXML($response);
          
          $xml = simplexml_import_dom($dom);
          $ns = $xml->getNamespaces(true);
          
          return [
              'formatted' => $dom->saveXML(),
              'erro' => (string) $xml->Body->children($ns['ns2'])->ExecutarResponse->result->erroExecucao
          ];
      } catch (Exception $e) {
          return ['erro' => 'Falha ao parsear XML: ' . $e->getMessage()];
      }
  }

}
