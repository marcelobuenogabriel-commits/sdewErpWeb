<?php

namespace Modules\Picking\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Picking\App\Models\PickingProducao;

class PalletController extends Controller
{

    protected $table_picking;

    public function __construct()
    {
        $this->middleware('auth');
        $this->table_picking = new PickingProducao();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('picking::pallet.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $codSep = $request->codSep;
        $codPal = $request->codPal;
        /*
         * Válida as informações enviadas pelo Coletor
         */
        if ($codSep == NULL || $codPal == NULL) {
            return 'Código de barras inválido!';
        }

        $codPro = $this->table_picking->getCodProSep($codSep);

        if($codPro) {
            /*
             * Realiza o backup da informação do Pallet na tela do Recebimento Pallet
             */
            $return_pallet = $this->table_picking->updatePallet($codPro, $codPal);

            if($return_pallet > 0) {
                $msg_return = 'Pallet excluído com sucesso!';
            } else {
                $msg_return = 'Pallet não excluído!';
            }
        }

        return \response()->json($msg_return);
    }
}
