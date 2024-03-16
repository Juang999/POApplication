<?php

namespace App\Http\Requests\Admin\SampleProduct;

use Illuminate\Foundation\Http\FormRequest;

class InputFabricTextureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sample_product_id' => 'required|integer',
            'fabric_description' => 'required|string',
            'fabric_photo' => 'required|string'
        ];
    }
}
