<?php

namespace Modules\WebService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class WebServiceSeniorXController extends Controller
{
    
    private $serviceUrl = 'https://platform.senior.com.br/t/senior.com.br/bridge/1.0/rest/platform/authentication/actions/login';

    private $username;
    private $password;

    public function __construct()
    {
        $this->middleware('auth');
        $this->username = env('KNAPP_SENIORX_USER');
        $this->password = env('KNAPP_SENIORX_PWD');
    }

    public function getToken() 
    {

        $body = [
            'username' => $this->username,
            'password' => $this->password
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($this->serviceUrl, $body);

        $token = json_decode($response['jsonToken'], true);

        return $token['access_token'];
    }

    public function solicitacoesVagas()
    {
        $token = $this->getToken();

        $serviceUrl = 'https://platform.senior.com.br/t/senior.com.br/bridge/1.0/rest/hcm/recruitment/queries/listFlowProcesses';

        $body = [
            'activeEmployeeId' => '03743FFB28024B6F97F90AA0D1E51E3F',
            'page' => 0,
            'size' => 100,
            'status' => 'IN_PROGRESS'
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json;charset=utf-8',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->get($serviceUrl, $body);
            
        $vagas = json_decode($response->body(), true);
        
        return $response->json();
    }
}
