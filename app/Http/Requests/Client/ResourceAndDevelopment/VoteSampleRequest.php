<?php

namespace App\Http\Requests\Client\ResourceAndDevelopment;

use Illuminate\Foundation\Http\FormRequest;

class VoteSampleRequest extends FormRequest
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
            'voting_event_id' => 'required|integer',
            'sample_id' => 'required|integer',
            'product_id' => 'required|integer',
            'voting' => 'nullable|integer',
            'score' => 'nullable|integer',
            'fiksasi' => 'nullable|integer',
            'note' => 'required|string',
        ];
    }
}
