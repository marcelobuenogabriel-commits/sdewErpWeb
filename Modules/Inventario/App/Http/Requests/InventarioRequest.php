<?php

namespace Modules\Inventario\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventarioRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'datInv' => 'required',
            'codDep' => 'required',
            'codPro' => 'required',
            'qtdPro' => 'required',
            'numCon' => 'required',
            'numDoc' => 'required'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
