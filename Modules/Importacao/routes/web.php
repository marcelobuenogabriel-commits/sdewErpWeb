<?php

use Illuminate\Support\Facades\Route;
use Modules\Importacao\App\Http\Controllers\ImportacaoController;

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
    Route::resource('importacao', ImportacaoController::class)->names('importacao');

    Route::get('importacao/{id}/produtos', [ImportacaoController::class, 'getProdutos'])->name('importacao.produtos');

    Route::put('importacao/{id}/updateprodutos', [ImportacaoController::class, 'updateinvoiceimportacao'])->name('importacao.updateprodutos');
    Route::delete('importacao/{id}/deleteprodutos/{produtoId}', [ImportacaoController::class, 'deleteprodutoinvoice'])->name('importacao.deleteprodutos');

    Route::post('importacao/invoice/listar', [ImportacaoController::class, 'emiteInvoice'])->name('importacao-emite-invoice');

    Route::post('importacao/invoice/document', [ImportacaoController::class, 'importDocument'])->name('importacao-documento');

    Route::get('importacao/invoice/endereco', [ImportacaoController::class, 'getEndereco'])->name('importacao.endereco');
    Route::put('importacao/{id}/updateendereco', [ImportacaoController::class, 'updateEndereco'])->name('importacao.updateendereco');

});
