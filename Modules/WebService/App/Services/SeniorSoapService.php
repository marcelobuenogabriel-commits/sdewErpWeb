<?php

namespace Modules\WebService\App\Services;

use Illuminate\Support\Facades\Http;
use SoapClient;
use Psr\Log\LoggerInterface;

class SeniorSoapService
{
    protected string $wsdl;
    protected array $options;
    protected array $config;
    protected ?LoggerInterface $logger;

    public function __construct(array $config = [], ?LoggerInterface $logger = null)
    {
        $default = config('webservice.senior', []);

        // Merge passed config with module config
        $this->config = array_merge($default, $config);

        $this->wsdl = $this->config['cad_usuario_wsdl'] ?? '';

        $this->options = [
            'trace' => true,
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'connection_timeout' => $this->config['timeout'] ?? 5,
        ];

        if (!empty($this->config['options']) && is_array($this->config['options'])) {
            $this->options = array_merge($this->options, $this->config['options']);
        }

        $this->logger = $logger;
    }

    /**
     * Create a SoapClient for a given call. Credentials and auth type can be overridden per-call via $callOptions.
     * $callOptions keys: user, password, auth_type (http|wsse|none), options (array to merge into SoapClient options)
     */
    protected function client(array $callOptions = []): SoapClient
    {
        $cfg = array_merge($this->config, $callOptions);

        $options = $this->options;
        if (!empty($cfg['options']) && is_array($cfg['options'])) {
            $options = array_merge($options, $cfg['options']);
        }

        // If HTTP basic auth requested
        $authType = strtolower($cfg['auth_type'] ?? ($this->config['auth_type'] ?? 'http'));
        if ($authType === 'http' && !empty($cfg['user'])) {
            $options['login'] = $cfg['user'];
            $options['password'] = $cfg['password'] ?? '';
        }

        // Always instantiate a fresh client for each call to allow different auth per-call
        $client = new SoapClient($this->wsdl, $options);

        // If WS-Security requested, attach header
        if ($authType === 'wsse' && !empty($cfg['user'])) {
            $header = $this->createWsseHeader($cfg['user'], $cfg['password'] ?? '');
            $client->__setSoapHeaders([$header]);
        }

        return $client;
    }

    /**
     * Generic __soapCall wrapper
     * @param string $operation
     * @param array $params
     * @param array $callOptions
     * @return mixed
     * @throws \Throwable
     */
    public function call(string $operation, array $params = [], array $callOptions = [])
    {
        try {
            $this->wsdl = $this->wsdl . $params['soap_url'] ?? '';
            $client = $this->client($params);
            
            try {
                $result = $client->__soapCall($operation, $params);
            } catch (\SoapFault $sf) {
                // Log SOAP fault details
                if ($this->logger) {
                    $this->logger->error('SeniorSoapService SOAP Fault', [
                        'operation' => $operation,
                        'faultcode' => $sf->faultcode,
                        'faultstring' => $sf->faultstring,
                        'detail' => $sf->detail,
                    ]);
                }
                throw $sf;
            }
            
            $result = $this->toArray($result);
            return $result;
        } catch (\Throwable $e) {
            if ($this->logger) {
                $this->logger->error('SeniorSoapService call failed', ['operation' => $operation, 'exception' => $e->getMessage(), 'callOptions' => $callOptions]);
            }

            throw $e;
        }
    }

    protected function toArray($value) {
        if (is_object($value) || is_array($value)) {
            // SoapVar / stdClass -> array
            $json = json_encode($value);
            return json_decode($json, true);
        }
        return $value;
    }

    protected function createWsseHeader(string $user, string $pass): \SoapHeader
    {
        $wsseNs = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

        $auth = new \stdClass();
        $auth->Username = new \SoapVar($user, XSD_STRING, null, null, 'Username', $wsseNs);
        $auth->Password = new \SoapVar($pass, XSD_STRING, null, null, 'Password', $wsseNs);

        $token = new \SoapVar($auth, SOAP_ENC_OBJECT, null, null, 'UsernameToken', $wsseNs);
        $security = new \SoapVar($token, SOAP_ENC_OBJECT, null, null, 'Security', $wsseNs);

        return new \SoapHeader($wsseNs, 'Security', $security, true);
    }

    /**
     * Flexible HTTP POST with custom headers, body, content-type and options
     * 
     * @param string $url Endpoint URL
     * @param string $body Raw body (e.g., XML string)
     * @param array $headers Custom headers (e.g., ['Content-Type' => 'text/xml', 'accept' => 'text/xml'])
     * @param array $options Additional options for Http:: (e.g., ['verify' => false])
     * @return array Parsed response as array
     * @throws \Throwable
     */
    public function callWithHttp(
        string $url,
        string $body = '',
        array $headers = [],
        array $options = []
    ): array {
        try {
            // Normalize headers and defaults
            $defaultHeaders = [
                'Content-Type' => 'text/xml',
                'Accept' => 'text/xml',
            ];

            $headers = array_merge($defaultHeaders, $headers);

            // Default options
            $defaultOptions = [
                'verify' => false,
            ];

            $options = array_merge($defaultOptions, $options);

            // Allow SOAPAction via options (options['soap_action']) or explicit header
            if (!empty($options['soap_action']) && !isset($headers['SOAPAction'])) {
                $headers['SOAPAction'] = $options['soap_action'];
            }

            // Log outgoing request for debugging
            if ($this->logger) {
                $this->logger->info('SeniorSoapService HTTP request', [
                    'url' => $url,
                    'headers' => $headers,
                    'options' => $options,
                    'body' => $body,
                ]);
            }

            $request = Http::withHeaders($headers)->withOptions($options);

            // Add basic auth if configured
            if (!empty($this->config['user']) && !empty($this->config['password'])) {
                $request = $request->withBasicAuth($this->config['user'], $this->config['password']);
            }

            $response = $request->withBody($body, 'text/xml')->post($url);

            // Log response for debugging
            if ($this->logger) {
                $this->logger->info('SeniorSoapService HTTP response', [
                    'url' => $url,
                    'status' => $response->status(),
                    'headers' => $response->headers(),
                    'body' => $response->body(),
                ]);
            }
        
            // Log on error
            if ($response->failed()) {
                if ($this->logger) {
                    $this->logger->error('SeniorSoapService HTTP call failed', [
                        'url' => $url,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
                $response->throw();
            }

            
            // Parse and return as array
            return $this->xmlToArray($response->body());
        } catch (\Throwable $e) {
            if ($this->logger) {
                $this->logger->error('SeniorSoapService HTTP call exception', [
                    'url' => $url,
                    'exception' => $e->getMessage(),
                ]);
            }
            throw $e;
        }
    }

    /*
    *   Monta o envelope SOAP com autenticação e parâmetros do corpo
    *   $operation: nome da operação SOAP
    *   $authParams: array com parâmetros de autenticação (user, password, encryption)
    *   $bodyParams: array com os parâmetros do corpo da requisição SOAP
    *   $subElement: nome do sub-elemento (opcional)
    */
    public function buildSoapEnvelope(string $operation, array $authParams, array $bodyParams, string $element, array $elementValues = [], string $subElement = '', array $subElementValues = []): string
    {
        $user = config('webservice.senior.user');
        $password = config('webservice.senior.password');
        $encryption = config('webservice.senior.encrypt', 0);

        $bodyXml = $this->arrayToXml($bodyParams, 'parameters', $element, $elementValues, $subElement, $subElementValues);

        return <<<XML
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://tempuri.org/">
        <soapenv:Body>
            <ser:{$operation}>
                <user>{$user}</user>
                <password>{$password}</password>
                <encryption>{$encryption}</encryption>
                {$bodyXml}
            </ser:{$operation}>
        </soapenv:Body>
        </soapenv:Envelope>
        XML;
    }

    /*
    *   Converte um array associativo em XML
    *   $data: array a ser convertido
    *   $rootElement: nome do elemento raiz (opcional)
    */
    private function arrayToXml(array $data, string $rootElement = '', string $element = '', array $elementValues = [], string $subElement = '', array $subElementValues = []): string
    {
        $xml = '';

        if ($rootElement !== '') {
            $xml .= "<{$rootElement}>";
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $xml .= $this->arrayToXml($value, $key);
            } else {
                $xml .= "<{$key}>{$value}</{$key}>";
            }
        }

        if ($element !== '') {
            $xml .= "<{$element}>";
        }

        foreach ($elementValues as $key => $value) {
            if (is_array($value)) {
                $xml .= $this->arrayToXml($value, $key);
            } else {
                $xml .= "<{$key}>{$value}</{$key}>";
            }
        }

        foreach ($subElementValues as $key => $value) {
            if ($subElement !== '') {
                $xml .= "<{$subElement}>";
            }

            if (is_array($value)) {
                $xml .= $this->arrayToXml($value, '');
            } else {
                $xml .= "<{$key}>{$value}</{$key}>";
            }
            
            if ($subElement !== '') {
                $xml .= "</{$subElement}>";
            }
        }
        
        if ($element !== '') {
            $xml .= "</{$element}>";
        }

        if ($rootElement !== '') {
            $xml .= "</{$rootElement}>";
        }

        return $xml;
    }

    /**
     * Parse XML response to array (simple implementation; customize as needed)
     */
    protected function xmlToArray(string $xml): array {
        // remove namespace prefixes (ex.: ns2:, S:, etc.)
        $xml = preg_replace('/(<\/?)[a-z0-9]+:/i', '$1', $xml);

        libxml_use_internal_errors(true);

        $sxml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($sxml === false) {
            return ['error' => 'Invalid XML'];
        }

        $json = json_encode($sxml);
        $arr = json_decode($json, true);

        return $arr;
    }
}