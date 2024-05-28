<?php

namespace App\Http\Requests\Client\Order;

use Illuminate\Foundation\Http\FormRequest;

class InputOrderRequest extends FormRequest
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
            'event_id' => 'required',
            'product_id' => 'required',
            'size_S' => 'nullable|integer',
            'size_M' => 'nullable|integer',
            'size_L' => 'nullable|integer',
            'size_XL' => 'nullable|integer',
            'size_XXL' => 'nullable|integer',
            'size_XXXL' => 'nullable|integer',
            'size_2' => 'nullable|integer',
            'size_4' => 'nullable|integer',
            'size_6' => 'nullable|integer',
            'size_8' => 'nullable|integer',
            'size_10' => 'nullable|integer',
            'size_12' => 'nullable|integer',
            'size_27' => 'nullable|integer',
            'size_28' => 'nullable|integer',
            'size_29' => 'nullable|integer',
            'size_30' => 'nullable|integer',
            'size_31' => 'nullable|integer',
            'size_32' => 'nullable|integer',
            'size_33' => 'nullable|integer',
            'size_34' => 'nullable|integer',
            'size_35' => 'nullable|integer',
            'size_36' => 'nullable|integer',
            'size_37' => 'nullable|integer',
            'size_38' => 'nullable|integer',
            'size_39' => 'nullable|integer',
            'size_40' => 'nullable|integer',
            'size_41' => 'nullable|integer',
            'size_42' => 'nullable|integer',
            'size_other' => 'nullable|integer',
            'total_order' => 'required|integer'
        ];
    }
}
