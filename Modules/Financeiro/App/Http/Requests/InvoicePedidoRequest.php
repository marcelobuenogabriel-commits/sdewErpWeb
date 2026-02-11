<?php

namespace Modules\Financeiro\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoicePedidoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if (array_key_exists('contratos', $_GET)) {
            return [
                'codEmp' => 'required',
                'numCtr' => 'required',
                'pedCli' => 'required',
                'nomUsu' => 'required',
                'vlrImp' => 'required',
                'vlrFee' => 'required',
                'datVct' => 'required',
                'tipPgt' => 'required',
                'desPro' => 'required'
            ];
        } else if (array_key_exists('pedidos', $_GET)) {
            return [
                'codEmp' => 'required',
                'codPed' => 'required',
                'pedCli' => 'required',
                'nomUsu' => 'required',
                'vlrImp' => 'required',
                'perFat' => 'required',
                'vlrFat' => 'required',
                'datVct' => 'required',
                'tipPgt' => 'required',
                'desPro' => 'required',
                'refPed' => 'required'
            ];
        } else {
            return [
                'codEmp' => 'required',
                'codCli' => 'required',
                'codMoe' => 'required',
                'cotMoe' => 'required',
                'datCot' => 'required'
            ];
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
