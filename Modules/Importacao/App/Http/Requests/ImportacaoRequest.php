<?php

namespace Modules\Importacao\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportacaoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'codEmp' => 'required|integer|different:0',
            'codPed' => 'required|integer|different:0',
            //'endEnt' => 'required|string|max:255',
            'ordBy'  => 'required|string|min:5|max:255',
            'sitInv' => 'required|integer|different:0',
            'tipInc' => 'required|string|different:0',
            //'terPay' => 'nullable|string|min:1|max:255',
            'madFor' => 'required|string|min:1|max:255',
            'numVol' => 'required|integer|min:1',
            'dimCax' => 'required|string|min:1|max:255',
            'gasTot' => 'nullable|string|min:0',
            'gasFre' => 'nullable|string|min:0',
            'gasSeg' => 'nullable|string|min:0',
            'gasDin' => 'nullable|string|min:0',
            'pesBru' => 'required|numeric|min:0',
            'pesLiq' => 'required|numeric|min:0'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function attributes()
    {
        return [
            'codEmp' => 'Código da Empresa',
            'codPed' => 'Código do Pedido',
            'endEnt' => 'Endereço de Entrega do Cliente do Pedido',
            'ordBy'  => 'Pedido por',
            'sitInv' => 'Situação da Invoice',
            'tipInc' => 'Visível',
            'terPay' => 'Termos de Pagamento',
            'madFor' => 'Forma de Pagamento',
            'numVol' => 'Quantidade de Volumes',
            'dimCax' => 'Dimensões da Carga (cm)',
            'gasTot' => 'Gastos Locais',
            'gasFre' => 'Gastos com Frete',
            'gasSeg' => 'Gastos com Seguro',
            'gasDin' => 'Gastos em Destino',
            'pesBru' => 'Peso Bruto (kg)',
            'pesLiq' => 'Peso Líquido (kg)'
        ];
    }
}
