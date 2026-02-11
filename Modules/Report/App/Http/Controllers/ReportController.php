<?php

namespace Modules\Report\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends Controller
{

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        if ($id == 'OC0001') {
            return redirect()->route('swoc001_view');
        } else if ($id == 'OC0002') {
            return redirect()->route('swoc002_view');
        } else if ($id == 'OC0003') {
            return redirect()->route('swoc003_view');
        } else if ($id == 'OC0004') {
            return redirect()->route('swoc004_view')->with(['id' => 'PRBR014']);
        } else if ($id == 'OC0005') {
            return redirect()->route('swoc004_view')->with(['id' => 'PRBR006']);
        } else if ($id == 'OC0006') {
            return redirect()->route('swoc004_view')->with(['id' => 'PRBR007']);
        } else if ($id == 'OC0007') {
            return redirect()->route('swoc005_view');
        }
    }
}
