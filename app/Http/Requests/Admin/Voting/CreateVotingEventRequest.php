<?php

namespace App\Http\Requests\Admin\Voting;

use Illuminate\Foundation\Http\FormRequest;

class CreateVotingEventRequest extends FormRequest
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
            'start_date' => 'required|date',
            'title' => 'required|string',
            'description' => 'required|string',
            'type' => 'required',
            'member_attendance_id' => 'required|string',
            'sample_id' => 'required|string'
        ];
    }
}
