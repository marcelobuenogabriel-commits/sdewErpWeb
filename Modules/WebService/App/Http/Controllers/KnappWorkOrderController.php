<?php

namespace Modules\WebService\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Exception;
use Psy\Util\Json;

class KnappWorkOrderController extends Controller
{
    /**
     * Configurações da API carregadas do arquivo de configuração
     */
    private array $config;

    /**
     * Constructor - Carrega e valida as configurações
     */
    public function __construct()
    {
        $this->loadAndValidateConfig();
    }

    /**
     * Carrega e valida as configurações da API
     *
     * @throws Exception
     */
    private function loadAndValidateConfig(): void
    {
        $this->config = Config::get('knapp_api', []);

        // Validação das configurações obrigatórias
        $requiredConfigs = [
            'client_id',
            'client_secret',
            'token_url',
            'api_base_url'
        ];

        foreach ($requiredConfigs as $configKey) {
            if (empty($this->config[$configKey])) {
                throw new Exception("Configuração obrigatória não encontrada: knapp_api.{$configKey}");
            }
        }

        // Define valores padrão para configurações opcionais
        $this->config = array_merge([
            'scope' => 'openid profile email samauth.ten samauth.skey idp_account',
            'http_timeout' => 30,
            'cache' => [
                'token_key' => 'knapp_api_access_token',
                'ttl' => 3300
            ]
        ], $this->config);

        Log::info('Configurações da API Knapp carregadas com sucesso');
    }

    /**
     * Obtém uma configuração específica
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Obtém um token de acesso OAuth2
     *
     * @return string|null
     * @throws Exception
     */
    public function getAccessToken(): ?string
    {
        $cacheKey = $this->getConfig('cache.token_key');

        // Verifica se já existe um token válido no cache
        $cachedToken = Cache::get($cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }

        try {
            $response = Http::timeout($this->getConfig('http_timeout'))
                ->asForm()
                ->post($this->getConfig('token_url'), [
                    'client_id' => $this->getConfig('client_id'),
                    'client_secret' => $this->getConfig('client_secret'),
                    'grant_type' => 'client_credentials',
                    'scope' => $this->getConfig('scope')
                ]);

            if (!$response->successful()) {
                Log::error('Erro ao obter token de acesso', [
                    'status' => $response->status(),
                    'url' => $this->getConfig('token_url')
                ]);
                throw new Exception('Falha na autenticação: ' . $response->status());
            }

            $tokenData = $response->json();

            if (!isset($tokenData['access_token'])) {
                Log::error('Token de acesso não encontrado na resposta');
                throw new Exception('Token de acesso não encontrado na resposta');
            }

            $accessToken = $tokenData['access_token'];
            $expiresIn = $tokenData['expires_in'] ?? 3600;

            // Armazena o token no cache com tempo de expiração (menos 5 minutos para segurança)
            $cacheTtl = min($expiresIn - 300, $this->getConfig('cache.ttl'));
            Cache::put($cacheKey, $accessToken, now()->addSeconds($cacheTtl));

            Log::info('Token de acesso obtido com sucesso');

            return $accessToken;

        } catch (Exception $e) {
            Log::error('Erro ao obter token de acesso', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Faz uma requisição autenticada para a API
     *
     * @param string $endpoint
     * @param array $data
     * @param string $method
     * @throws Exception
     */
    private function makeAuthenticatedRequest(string $endpoint, array $data = [], string $method = 'POST')
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            throw new Exception('Não foi possível obter token de acesso');
        }

        try {
            $url = rtrim($this->getConfig('api_base_url'), '/') . '/' . ltrim($endpoint, '/');

            $httpClient = Http::timeout($this->getConfig('http_timeout'))
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                    'Accept' => '*/*',
                    'Cookie' => ''
                ]);

            $response = match (strtoupper($method)) {
                'GET' => $httpClient->get($url, $data),
                'POST' => $httpClient->post($url, $data),
                'PUT' => $httpClient->put($url, $data),
                'DELETE' => $httpClient->delete($url, $data),
                default => throw new Exception('Método HTTP não suportado: ' . $method)
            };


            if (!$response->successful()) {
                Log::error('Erro na requisição para API', [
                    'url' => $url,
                    'method' => $method,
                    'status' => $response->status()
                ]);

                // Se o erro for 401, limpa o token do cache para forçar renovação
                if ($response->status() === 401 || $response->status() === 500) {
                    Cache::forget($this->getConfig('cache.token_key'));
                }

                throw new Exception('Erro na API: ' . $response->status());
            } else {
                return json_decode($response);
            }
        } catch (Exception $e) {
            Log::error('Erro na requisição autenticada', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Testa a conectividade com a API e valida as configurações
     *
     * @return JsonResponse
     */
    public function testConnection(): JsonResponse
    {
        try {
            // Testa se as configurações estão válidas
            $this->loadAndValidateConfig();

            // Testa se consegue obter um token
            $token = $this->getAccessToken();

            return response()->json([
                'success' => true,
                'message' => 'Conexão com a API estabelecida com sucesso',
                'token_obtained' => !empty($token),
                'config_status' => 'valid',
                'api_base_url' => $this->getConfig('api_base_url'),
                'timestamp' => now()->toISOString()
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao conectar com a API',
                'error' => $e->getMessage(),
                'config_status' => 'invalid',
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Retorna informações sobre a configuração atual (sem dados sensíveis)
     *
     * @return JsonResponse
     */
    public function getConfigInfo(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'config' => [
                    'api_base_url' => $this->getConfig('api_base_url'),
                    'token_url' => $this->getConfig('token_url'),
                    'scope' => $this->getConfig('scope'),
                    'http_timeout' => $this->getConfig('http_timeout'),
                    'cache_ttl' => $this->getConfig('cache.ttl'),
                    'client_id_configured' => !empty($this->getConfig('client_id')),
                    'client_secret_configured' => !empty($this->getConfig('client_secret'))
                ],
                'timestamp' => now()->toISOString()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter informações de configuração',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Limpa o token do cache (útil para forçar renovação)
     *
     * @return JsonResponse
     */
    public function clearTokenCache(): JsonResponse
    {
        $cacheKey = $this->getConfig('cache.token_key');
        Cache::forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Cache do token limpo com sucesso',
            'cache_key' => $cacheKey,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Consulta Work Orders na API
     *
     * @param Request $request
     */
    public function getWorkOrders(Request $request)
    {

        try {
            // Parâmetros de entrada com valores padrão
            $workOrderName = $request->input('work_order_name', '');
            $productName = $request->input('product_name', '');
            $locationName = $request->input('location_name', '');
            $status = $request->input('status', [['key' => '3', 'listItemAction' => 'Add', 'value' => '3']]);
            $skip = $request->input('skip', 0);
            $limit = $request->input('limit', 40);
            $defaultSortOrder = $request->input('default_sort_order', 'Y');
            $dataLst = $request->input('DataLst', ['name' => '']);

            // Monta o payload da requisição
            $payload = [
                'Input' => [
                    'WorkOrderName' => $workOrderName,
                    'ProductName' => $productName,
                    'Status' => $status,
                    'LocationName' => $locationName,
                    'Skip' => $skip,
                    'Limit' => $limit,
                    'DefaultSortOrder' => $defaultSortOrder,
                    'QueryOptions' => [
                        'SortingLst' => [],
                        'skip' => $skip,
                        'limit' => $limit
                    ]
                ],
                '__RequestData' => [
                    "DataLst" => $dataLst,
                ],
            ];

            // Faz a requisição para a API
            $response = $this->makeAuthenticatedRequest('WorkOrderInquiryService/Execute', $payload);

            // Retorna como resposta JSON estruturada
            return response()->json([
                'success' => true,
                'data' => $response,
                'request_params' => [
                    'work_order_name' => $workOrderName,
                    'product_name' => $productName,
                    'location_name' => $locationName,
                    'status' => $status,
                    'skip' => $skip,
                    'limit' => $limit
                ],
                'timestamp' => now()->toISOString()
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao consultar Work Orders', [
                'error' => $e->getMessage(),
                'request_data' => $request->except(['client_secret', 'password'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar Work Orders',
                'error' => $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Consulta Work Orders com filtros específicos
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function searchWorkOrders(Request $request): JsonResponse
    {
        // Validação dos parâmetros de entrada
        $validated = $request->validate([
            'work_order_name' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
            'location_name' => 'nullable|string|max:255',
            'status' => 'nullable|array',
            'status.*.key' => 'required_with:status|string',
            'status.*.value' => 'required_with:status|string',
            'status.*.listItemAction' => 'nullable|string|in:Add,Remove',
            'skip' => 'nullable|integer|min:0',
            'limit' => 'nullable|integer|min:1|max:100',
            'default_sort_order' => 'nullable|string|in:Y,N'
        ]);

        return $this->getWorkOrders($request);
    }

    /**
     * Obtém detalhes de um Work Order específico
     *
     * @param string $workOrderName
     */
    public function getWorkOrderDetails(string $workOrderName)
    {
        try {
            $request = new Request([
                'work_order_name' => $workOrderName,
                'limit' => 1
            ]);

            $response = $this->getWorkOrders($request);
            $responseData = $response->getData(true);

            if (!$responseData['success']) {
                return $response;
            }

            // Verifica se encontrou o Work Order
            $workOrders = $responseData['data']['dataLst']['__Value'] ?? [];

            if (empty($workOrders)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work Order não encontrado',
                    'work_order_name' => $workOrderName,
                    'timestamp' => now()->toISOString()
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $workOrders,
                'work_order_name' => $workOrderName,
                'timestamp' => now()->toISOString()
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao obter detalhes do Work Order', [
                'work_order_name' => $workOrderName,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter detalhes do Work Order',
                'error' => $e->getMessage(),
                'work_order_name' => $workOrderName,
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Obtém Work Orders por status
     *
     * @param string $statusKey
     * @param string $statusValue
     * @return JsonResponse
     */
    public function getWorkOrdersByStatus(string $statusKey, string $statusValue = null): JsonResponse
    {
        try {
            $statusValue = $statusValue ?? $statusKey;

            $request = new Request([
                'status' => [
                    [
                        'key' => $statusKey,
                        'listItemAction' => 'Add',
                        'value' => $statusValue
                    ]
                ]
            ]);

            return $this->getWorkOrders($request);

        } catch (Exception $e) {
            Log::error('Erro ao consultar Work Orders por status', [
                'status_key' => $statusKey,
                'status_value' => $statusValue,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar Work Orders por status',
                'error' => $e->getMessage(),
                'status_key' => $statusKey,
                'status_value' => $statusValue,
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Obtém Work Orders paginados
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getWorkOrdersPaginated(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $page = $validated['page'] ?? 1;
        $perPage = $validated['per_page'] ?? 40;
        $skip = ($page - 1) * $perPage;

        $request->merge([
            'skip' => $skip,
            'limit' => $perPage
        ]);

        $response = $this->getWorkOrders($request);
        $responseData = $response->getData(true);

        if ($responseData['success']) {
            $responseData['pagination'] = [
                'current_page' => $page,
                'per_page' => $perPage,
                'skip' => $skip
            ];
        }

        return response()->json($responseData, $response->status());
    }
}
