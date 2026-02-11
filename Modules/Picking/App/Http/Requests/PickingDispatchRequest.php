<?php

namespace Modules\Picking\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PickingDispatchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if($this->request->has('search')) {
            return [
                'codmov' => 'required'
            ];
        } else if($this->request->has('create')) {
            return [
                'numpro' => 'required|max:5'
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
