<?php

use Illuminate\Support\Facades\Route;
use Modules\KPI\App\Http\Controllers\KPIController;

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
    Route::get('kpi/headcount', [KPIController::class, 'Headcount'])->name('kpi.headcount');

    Route::post('kpi/headcount', [KPIController::class, 'Headcount'])->name('kpi.headcount.post');

    Route::get('kpi/systemcomparison', [KPIController::class, 'SystemsComparison'])->name('kpi.systemcomparison');
    Route::post('kpi/systemcomparison', [KPIController::class, 'SystemsComparison'])->name('kpi.system.comparison.post');

    Route::get('kpi/salarycomparison', [KPIController::class, 'SalaryComparison'])->name('kpi.salarycomparison');
    Route::post('kpi/salarycomparison', [KPIController::class, 'SalaryComparison'])->name('kpi.salary.comparison.post');
});
