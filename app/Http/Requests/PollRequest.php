<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ];
    }
}
