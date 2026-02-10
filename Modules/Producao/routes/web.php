<?php

use Illuminate\Support\Facades\Route;
use Modules\Producao\App\Http\Controllers\ProducaoController;
use Modules\Producao\App\Http\Controllers\OrdemProducaoController;
use \Modules\Producao\App\Http\Controllers\MovimentacaoController;

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
    Route::get('producao/agregationorder', 'ProducaoController@agregationOrder')->name('agregationorder');
    Route::post('producao/executeproc', 'ProducaoController@executeproc')->name('executeproc');
    Route::get('producao/handlingunit/{id}', 'ProducaoController@handlingUnit')->name('handlingunit');
    Route::get('printorder/{topsl}/{numorp}', [OrdemProducaoController::class, 'show'])->name('printorder.showorder');
    Route::resource('producao', ProducaoController::class)->names('producao');
    Route::resource('printorder', OrdemProducaoController::class)->names('printorder');

    Route::post('/set-bench', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'bench' => 'required|in:BA01,BA02,BA03,BA04,BA05,BA06,BA07,BA08,BA09,BA10,BA11'
        ]);

        session()->put('bench', $request->bench);

        return back()->with('success', 'Bancada definida: ' . $request->bench);
    })->name('setBench');;

    Route::post('/movimentacao', [MovimentacaoController::class, 'salvarmovimentacao'])->name('movimentacao.salvarmovimentacao');
    Route::get('/finalizar-ordem/{codMov}/{numOrp}', [MovimentacaoController::class, 'finalizarOrdem'])
        ->name('finalizarOrdem');
    Route::get('/sucesso', [MovimentacaoController::class, 'paginaSucesso'])->name('sucesso');
    Route::get('/falha/{numOrp}', [MovimentacaoController::class, 'paginaFalha'])->name('falha');

});
