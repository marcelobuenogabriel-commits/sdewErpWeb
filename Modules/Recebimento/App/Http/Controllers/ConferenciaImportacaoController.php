<?php

namespace Modules\Recebimento\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Recebimento\App\Models\Recebimento;

class ConferenciaImportacaoController extends Controller
{

    protected $table;

    public function __construct(Recebimento $recebimento) {
        $this->middleware('auth');
        $this->table = $recebimento;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $list_itens = $this->table->getItensImportacao();
        return view('recebimento::conferencia.index', compact('list_itens'), 
            [
                'title' => 'Conferência de Importação',
                'description' => 'Gerencie as conferências de importação de mercadorias.'
            ]
        );
    }
}
