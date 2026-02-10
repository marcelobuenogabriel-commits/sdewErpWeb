<?php

namespace Modules\Producao\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgregacaoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'codEmp' => 'required|integer|different:0',
            'codFil' => 'required|integer|different:0',
            'numPro'  => 'required|string|min:5|max:255',
            'codFam' => 'required|string|min:5|max:15',
            'codSta' => 'nullable|string',
            'codIdPr' => 'nullable|string'
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
            'codFil' => 'Código do Filial',
            'numPro' => 'Projeto Z',
            'codFam' => 'Família',
            'codSta' => 'Station',
            'codIdPr'=> 'ID Peça Resultante'
        ];
    }
}
