<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\App\Http\Controllers\ReportController;

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
    Route::resource('report', ReportController::class)->names('report');

    Route::get('reports/OC0001', 'PickingReportController@SWOC001View')->name('swoc001_view');
    Route::post('reports/OC0001', 'PickingReportController@SWOC001')->name('swoc001');

    Route::get('reports/OC0002', 'PickingReportController@SWOC002View')->name('swoc002_view');
    Route::post('reports/OC0002', 'PickingReportController@SWOC002')->name('swoc002');

    Route::get('reports/OC0003', 'PickingReportController@SWOC003View')->name('swoc003_view');
    Route::post('reports/OC0003', 'PickingReportController@SWOC003')->name('swoc003');

    Route::get('reports/OC0004', 'PickingReportController@SWOC004View')->name('swoc004_view');
    Route::post('reports/OC0004', 'PickingReportController@SWOC004')->name('swoc004');

    Route::get('reports/OC0005', 'PickingReportController@SWOC005View')->name('swoc005_view');
    Route::post('reports/OC0005', 'PickingReportController@SWOC005')->name('swoc005');
});
