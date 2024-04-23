<?php

namespace App\Http\Requests\Admin\SampleProduct;

use Illuminate\Foundation\Http\FormRequest;

class SampleProductRequest extends FormRequest
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
            'date' => 'required|date',
            'date_tailor' => 'required',
            'reference_sample_id' => 'nullable|integer',
            'article_name' => 'required|string',
            'entity_name' => 'required|string',
            'style_id' => 'required|integer',
            'sub_style_id' => 'nullable|integer',
            'size' => 'required|string',
            'note_description' => 'nullable|string',
            'design_file' => 'nullable|string',
            'designer_id' => 'nullable|integer',
            'md_id' => 'nullable|integer',
            'leader_designer_id' => 'nullable|integer',
            'photo' => 'required|string',
            'sample_design' => 'required|string',
            'material_id' => 'required|string',
            'material_additional_id' => 'required|string',
            'accessories_id' => 'required|string',
            'accessories_product_id' => 'required|string',
        ];
    }
}
