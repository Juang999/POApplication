<?php

namespace App\Http\Requests\Admin\SampleProduct;

use Illuminate\Foundation\Http\FormRequest;

class CreateClothesRequest extends FormRequest
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
            'grade' => 'required|string',
            'size' => 'required|string',
            'entity_name' => "required|string",
            "product_order" => "required",
            "category" => "required|string",
            "article_name" => "required|string",
            "grade" => "required|string",
            "combo" => "required|string",
            "special_feature" => "required|string",
            "keyword" => "required|string",
            "description" => "required|string",
            "group_article" => "required",
            "type_id" => "required",
            "style_id" => "required",
            "sub_style_id" => "required",
            'product_color' => 'required|string',
        ];
    }
}
