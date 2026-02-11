<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\WebService\App\Http\Controllers\KnappWorkOrderController;
/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::get('webservice', fn (Request $request) => $request->user())->name('webservice');
});

Route::prefix('knapp-api')->group(function () {
    // Rota para testar a conexão com a API
    Route::get('/test-connection', [KnappWorkOrderController::class, 'testConnection']);

    // Rota para limpar o cache do token de acesso
    Route::post('/clear-token-cache', [KnappWorkOrderController::class, 'clearTokenCache']);

    // Rota principal para consultar Work Orders com filtros
    Route::post('/work-orders', [KnappWorkOrderController::class, 'getWorkOrders']);

    // Rota para buscar Work Orders (usa a mesma lógica, mas com validação)
    Route::post('/work-orders/search', [KnappWorkOrderController::class, 'searchWorkOrders']);

    // Rota para obter detalhes de um Work Order específico
    Route::get('/work-orders/{workOrderName}', [KnappWorkOrderController::class, 'getWorkOrderDetails']);

    // Rota para obter Work Orders por status
    Route::get('/work-orders/status/{statusKey}/{statusValue?}', [KnappWorkOrderController::class, 'getWorkOrdersByStatus']);

    // Rota para obter Work Orders com paginação
    Route::get('/work-orders/paginated', [KnappWorkOrderController::class, 'getWorkOrdersPaginated']);
});
