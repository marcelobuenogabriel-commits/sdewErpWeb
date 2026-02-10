<?php

namespace Modules\Producao\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\AgrupaOrdemProducaoJob;
use App\Notifications\JobFinalizadoNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Modules\Producao\App\Http\Requests\AgregacaoRequest;
use Modules\Producao\App\Models\PLCAPP03;
use Modules\Producao\App\Models\TKAEPDG;
use Modules\WebService\App\Http\Controllers\KnappWorkOrderController;

class ProducaoController extends Controller
{
    private string $baseUrl;
    private array $defaultHeaders;

    public function __construct(string $baseUrl = 'https://erpweb.knapp.at/api/knapp-api')
    {
        $this->middleware('auth');
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];
    }

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
    public function show($id)
    {
        //
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

    public function handlingUnit($id)
    {
        /**********************************************************************************************/
        /* Realizo o handling unit da ordem de produção                                               */
        /* Realizo a baixa da ordem                                                                   */
        /* Realizo a verfiicação de testes de bancadas para validação do handling unit (se necessário */
        /**********************************************************************************************/

        // Verifica se o usuário autenticado tem uma bancada na sessão
        $showPopup = !session()->has('bench'); // Define se o modal deve ser exibido ou não
        $availableBenches = ['BA01', 'BA02', 'BA03', 'BA04', 'BA05', 'BA06', 'BA07', 'BA08', 'BA09', 'BA10', 'BA11'];
        $infoOrder = [];
        $mensagemTestes = "";
        $booleanTeste = false;

        $resultTest = $this->testConnection();
        if($resultTest["http_code"] == 200 && !$showPopup)
        {
            $message = $resultTest["data"]["message"];
            $idordem = (string) $id;

            $resultWorkDetails = new JsonResponse($this->getWorkOrderDetails($idordem));

            if ($resultWorkDetails instanceof JsonResponse) {
                $responseData = $resultWorkDetails->getData(true); // Retorna como array associativo
                $httpCode = $resultWorkDetails->getStatusCode();

                if($httpCode == 200){
                    $infoOrder = json_decode($responseData['data']['data']);

                    $service = new TesteBancadaController();

                    if($infoOrder == []){
                        $booleanTeste = TRUE;
                        $mensagemTestes = $service->validarTestesOrdem($idordem);
                        if ($mensagemTestes["status"]) {
                            $booleanTeste = TRUE;
                            $mensagemTestes = $mensagemTestes['mensagens'];
                        } else {
                            $booleanTeste = FALSE;
                            $mensagemTestes = $mensagemTestes['mensagens'];
                        }
                    } else {
                        //Primeiro verifico se esta ordem já possui testes realizados
                        $mensagemTestes = $service->validarTestesOrdem($idordem);

                        if ($mensagemTestes["status"]) {
                            $booleanTeste = TRUE;
                            $mensagemTestes = $mensagemTestes['mensagens'];
                        } else {
                            $booleanTeste = FALSE;
                            $mensagemTestes = $mensagemTestes['mensagens'];
                        }
                    }
                } else {
                    return [
                        'http_code' => 500,
                        'data' => null,
                        'raw_response' => 'Resposta inesperada do controller.'
                    ];
                }
            } else {
                return [
                    'http_code' => 500,
                    'data' => null,
                    'raw_response' => 'Resposta inesperada do controller.'
                ];
            }

        } else {
            $message = $resultTest["data"]["message"];
            $idordem = "";
        }

        return view('producao::show', compact("message", "idordem", "infoOrder", "mensagemTestes", "booleanTeste", "showPopup",
            "availableBenches"));
    }

    public function agregationOrder(){
        return view('producao::agregationorder');
    }

    public function executeproc(AgregacaoRequest $request)
    {
        $data = $request->all();

        $xNumPro = $data['numPro'];
        $xCodFam = $data['codFam'];
        $xCodSta = $data['codSta'];
        $xCodIdPr = $data['codIdPr'];

        AgrupaOrdemProducaoJob::dispatch(
            $xNumPro,
            $xCodFam,
            $xCodSta,
            $xCodIdPr
        );

        session()->flash('success', 'Processo enviado à JOB! A mensagem será recebida no e-mail após o término.');
        return view('producao::agregationorder');

    }

    public function setBench(Request $request)
    {
        // Valida a entrada
        $request->validate([
            'bench' => 'required|in:BA01,BA02,BA03,BA04,BA05,BA06,BA07,BA08,BA09,BA10,BA11'
        ]);

        // Salva a bancada na sessão
        session(['bench' => $request->bench]);

        // Redireciona para a mesma página com uma mensagem de sucesso
        return redirect()->back()->with('success', 'Bancada selecionada: ' . $request->bench);
    }

    /**
     * Faz uma requisição HTTP usando cURL
     */
    private function makeRequest(string $endpoint, string $method = 'GET', array $data = null): array
    {

        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->defaultHeaders,
            CURLOPT_SSL_VERIFYPEER => true, // Para desenvolvimento local
        ]);

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);

        curl_close($curl);

        if ($error) {
            throw new Exception("Erro cURL: " . $error);
        }

        $decodedResponse = json_decode($response, true);

        return [
            'http_code' => $httpCode,
            'data' => $decodedResponse,
            'raw_response' => $response
        ];
    }

    /**
     * Testa a conexão com a API
     */
    public function testConnection(): array
    {
        return $this->makeRequest('test-connection');
    }

    /**
     * Consulta Work Orders com filtros
     */
    public function getWorkOrders(array $filters = []): array
    {
        return $this->makeRequest('work-orders', 'POST', $filters);
    }

    /**
     * Busca Work Orders com validação
     */
    public function searchWorkOrders(array $filters = []): array
    {
        return $this->makeRequest('work-orders/search', 'POST', $filters);
    }

    /**
     * Obtém detalhes de um Work Order específico
     */
    public function getWorkOrderDetails(string $workOrderName): array
    {

        return $this->makeRequest("work-orders/{$workOrderName}");
    }

    /**
     * Consulta Work Orders por status
     */
    public function getWorkOrdersByStatus(string $statusKey, string $statusValue = null): array
    {
        $statusValue = $statusValue ?? $statusKey;
        return $this->makeRequest("work-orders/status/{$statusKey}/{$statusValue}");
    }

    /**
     * Consulta Work Orders com paginação
     */
    public function getWorkOrdersPaginated(int $page = 1, int $perPage = 40): array
    {
        return $this->makeRequest("work-orders/paginated?page={$page}&per_page={$perPage}");
    }

    /**
     * Limpa o cache do token
     */
    public function clearTokenCache(): array
    {
        return $this->makeRequest('clear-token-cache', 'POST');
    }

}
