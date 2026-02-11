<?php

namespace Modules\Inventario\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Inventario\App\Http\Requests\InventarioRequest;
use Modules\Inventario\App\Models\Inventario;
use Modules\WebService\App\Http\Controllers\WebServiceController;

class InventarioController extends Controller
{

    private $table;

    protected $web_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->table = new Inventario();
        $this->web_service = new WebServiceController();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inventario = $this->table->findAll();

        return view('inventario::index', compact('inventario'));
    }

    /**
     * Show the specified resource.
     */
    public function showInventario(Request $request)
    {
        $datInv = $request->segment('2');
        $codDep = $request->segment('4');
        $conInv = $request->segment('6');

        $itens_inventario = $this->table->findAllItens($datInv, $codDep, $conInv);

        return view('inventario::show', compact('itens_inventario', 'datInv', 'codDep'));
    }

    public function showFormContage(Request $request)
    {
        $datInv = $request->segment('2');
        $codDep = $request->segment('4');
        $numCon = $request->segment('6');
        $codPro = $request->segment('8');

        return view('inventario::contagem', compact('datInv', 'codDep', 'codPro', 'numCon'));
    }

    public function contage(InventarioRequest $request)
    {
        $datInv = $request->datInv;
        $codDep = $request->codDep;
        $codPro = $request->codPro;
        $qtdPro = $request->qtdPro;
        $numCon = $request->numCon;
        $numDoc = $request->numDoc;

        /*
         * Se depósito for 219 então soma-se a qtd do campo qtdrae
         *              (Itens Reserva Exclusiva)
         */
        if ($codDep == 219) {
            $resExc = $this->table->getReservaExclusiva($codPro);

            $qtdPro = $qtdPro + $resExc;
        }

        $parans = [
            'codEmp' => 1,
            'datInv' => $datInv,
            'codDep' => $codDep,
            'numCon' => $numCon,
            'tipInv' => 0,
            'numDoc' => $numDoc,
            'codMod' => 0,
            'codPro' => $codPro,
            'codDer' => '',
            'qtdCon' => $qtdPro
        ];

        $return_web_service = $this->web_service->contageInvent($parans);

        if ($return_web_service->code == '200') {
            $this->table->addMotAcerto($parans);

            return redirect()->back()->with('success', 'Contagem Lançada com sucesso.');
        } else {
            return redirect()->back()->with('error', 'Problemas ao registrar a contagem.');
        }
    }
}
