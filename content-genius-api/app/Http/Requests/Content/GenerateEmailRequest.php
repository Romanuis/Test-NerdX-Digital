<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class GenerateEmailRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'purpose' => ['required', 'string', 'min:10', 'max:1000'],
            'tone' => ['sometimes', 'string', 'in:professional,casual,formal,friendly'],
            'recipient_name' => ['sometimes', 'string', 'max:100'],
            'sender_name' => ['sometimes', 'string', 'max:100'],
            'additional_info' => ['sometimes', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'purpose.required' => 'Email purpose is required.',
            'purpose.min' => 'Purpose must be at least 10 characters.',
            'purpose.max' => 'Purpose cannot exceed 1000 characters.',
            'tone.in' => 'Tone must be one of: professional, casual, formal, friendly.',
        ];
    }
}
