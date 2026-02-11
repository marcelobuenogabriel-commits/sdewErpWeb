<?php

use Illuminate\Support\Facades\Route;
use Modules\Recebimento\App\Http\Controllers\RecebimentoController;
use \Modules\Recebimento\App\Http\Controllers\ConferenciaController;
use Modules\Recebimento\App\Http\Controllers\ConferenciaImportacaoController;
use Modules\Recebimento\App\Http\Controllers\RecebimentoImportacaoController;
use Modules\Recebimento\App\Http\Controllers\PreFaturaController;
use Tests\Feature\RecebimentoImportacaoTest;

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
    Route::post('conferencia/storetest', 'ConferenciaController@storeTest')->name('test');
    Route::resource('recebimento', RecebimentoController::class)->except(['show'])->names('recebimento');
    Route::resource('conferencia', ConferenciaController::class)->names('conferencia')->except(['show']);
    Route::resource('conferenciaimp', ConferenciaImportacaoController::class)->names('conferenciaimp');
    Route::post('recebimento/etiqueta', 'RecebimentoController@PrintTag')->name('print_tag');
    Route::post('recebimento/close', 'RecebimentoController@CloseNfc')->name('close_nfc');
    Route::post('recebimento/change', 'RecebimentoController@ChangeOcp')->name('change_ocp');
    Route::get('recebimento/adicionaOc', [RecebimentoController::class, 'adicionaOc'])->name('adiciona_oc');
    Route::post('recebimento/updateOcp', 'RecebimentoController@UpdateOcp')->name('update_ocp');

    Route::post('recebimento/removeNfc', 'RecebimentoController@RemoveNfc')->name('remove_nfc');

    Route::post('conferencia/etiqueta', 'ConferenciaController@PrintTag')->name('conferencia.print_tag');

    Route::post('conferencia/createPallet', 'ConferenciaController@CreatePallet')->name('conferencia.create_pallet');
    Route::get('conferencia/pallet', [ConferenciaController::class, 'Pallet'])->name('conferencia.pallet');

    Route::get('recebimento/conferencia', [RecebimentoController::class, 'conferencia'])->name('recebimento.conferencia');

    Route::get('recebimento/prefatura', [PreFaturaController::class, 'index'])->name('recebimento.prefatura');
    Route::get('recebimento/prefatura/{numane}/{numpfa}', [PreFaturaController::class, 'show'])->name('recebimento.prefatura.show');
    Route::post('recebimento/prefatura/gerar', [PreFaturaController::class, 'gerarEmbalagemPreFatura'])->name('recebimento.prefatura.gerar_embalagens');

    Route::get('recebimento/prefatura/impressao', [PreFaturaController::class, 'impressaoEtiquetas'])->name('recebimento.prefatura.impressao');
});

