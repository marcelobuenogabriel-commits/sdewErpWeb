<?php

namespace Modules\Financeiro\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'codEmp' => 'required',
            'codPed' => 'required',
            'pedCli' => 'required',
            'nomUsu' => 'required',
            'vlrImp' => 'required',
            'VlrFee' => 'required',
            'perFat' => 'required',
            'vlrFat' => 'required',
            'codMoe' => 'required',
            'cotMoe' => 'nullable',
            'datVct' => 'required',
            'tipPgt' => 'required',
            'desPro' => 'required',
            'refPed' => 'required'
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
