<?php

use Illuminate\Support\Facades\Route;
use Modules\Financeiro\App\Http\Controllers\FinanceiroController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::resource('financeiro', FinanceiroController::class)->names('financeiro');

    Route::get('financeiro/invoice/contratos', 'InvoiceController@index')->name('inv-contratos');
    Route::get('financeiro/invoice/pedidos', 'InvoiceController@index')->name('inv-pedidos');

    Route::post('financeiro/invoice/pedidos/consulta', 'InvoiceController@consultaPedidos')->name('consulta-pedidos');
    Route::post('financeiro/invoice/pedidos/consulta/pedido', 'InvoiceController@consultaPedido')->name('consulta-pedido');

    Route::post('financeiro/invoice/contratos/consulta', 'InvoiceController@consultaContratos')->name('consulta-contratos');
    Route::post('financeiro/invoice/contratos/consulta/contrato', 'InvoiceController@consultaContrato')->name('consulta-contrato');

    Route::post('financeiro/invoice/pedido/listar', 'InvoiceController@emiteInvoice')->name('emite-invoice');

    Route::group([], function(){
       Route::resource('invoice', \Modules\Financeiro\App\Http\Controllers\InvoiceController::class)->names('invoice');
    });
});
