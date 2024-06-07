<?php

namespace App\Http\Requests\Admin\SampleProduct;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSampleProductRequest extends FormRequest
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
            'date' => 'nullable|date',
            'reference_sample_id' => 'nullable|integer',
            'article_name' => 'nullable|string',
            'style_id' => 'nullable|integer',
            'sub_style_id' => 'nullable|integer',
            'entity_name' => 'nullable|string',
            'material' => 'nullable|string',
            'size' => 'nullable|string',
            'accessories' => 'nullable|string',
            'note_description' => 'nullable|string',
            'design_file' => 'nullable|string',
            'designer_id' => 'nullable|integer',
            'md_id' => 'nullable|integer',
            'leader_designer_id' => 'nullable|integer',
            'note_and_description_2' => 'nullable|string',
        ];
    }
}
