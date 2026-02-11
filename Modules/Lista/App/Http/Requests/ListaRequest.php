<?php

namespace Modules\Lista\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'codPlt' => 'required',
            'seqPro' => 'required'
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
