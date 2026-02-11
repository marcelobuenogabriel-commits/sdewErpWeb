<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventario\App\Http\Controllers\InventarioController;

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
    Route::resource('inventario', InventarioController::class)->names('inventario');

    Route::get('/inventario/{id}/deposito/{dep}/contagem/{cont}', 'InventarioController@showInventario')->name('show_inventario');
    Route::get('/inventario/{id}/deposito/{dep}/contagem/{cont}/produto/{prod}', 'InventarioController@showFormContage')->name('contagem_inventario');
    Route::post('inventario/contage', 'InventarioController@contage')->name('contage');
});
