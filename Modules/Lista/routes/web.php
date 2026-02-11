<?php

use Illuminate\Support\Facades\Route;
use Modules\Lista\App\Http\Controllers\ListaController;

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
    Route::resource('lista', ListaController::class)->names('lista');

    Route::post('lista/itens', 'ListaController@showItensLista')->name('lista-ite');
    Route::post('lista/itens/sep', 'ListaController@showItensLiv')->name('lista-liv');
    Route::post('lista/itens/set', 'ListaController@setQtdaltante')->name('lista-set');
    Route::post('lista/close', 'ListaController@finalizaLista')->name('lista-close');
    Route::post('lista/cancel', 'ListaController@cancelaLista')->name('lista-cancel');
});
