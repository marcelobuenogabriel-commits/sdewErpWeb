<?php

use Illuminate\Support\Facades\Route;
use Modules\Picking\App\Http\Controllers\PickingController;
use Modules\Picking\App\Http\Controllers\DispatchController;
use Modules\Picking\App\Http\Controllers\CPartsController;
use Modules\Picking\App\Http\Controllers\InsumosController;
use Modules\Picking\App\Http\Controllers\PalletController;

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
    Route::resource('picking', PickingController::class)->names('picking');
    Route::resource('dispatch', DispatchController::class);
    Route::resource('cparts', CPartsController::class);
    Route::resource('insumos', InsumosController::class);
    Route::resource('pallet', PalletController::class);
    Route::post('dispatch/store_dispatch', 'DispatchController@StoreDispatch')->name('dispatch.store_dispatch');
});
