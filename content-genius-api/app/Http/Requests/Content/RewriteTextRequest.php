<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class RewriteTextRequest extends FormRequest
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
            'text' => ['required', 'string', 'min:20', 'max:5000'],
            'tone' => ['sometimes', 'string', 'in:professional,casual,academic,creative,persuasive'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'text.required' => 'Text to rewrite is required.',
            'text.min' => 'Text must be at least 20 characters.',
            'text.max' => 'Text cannot exceed 5000 characters.',
            'tone.in' => 'Tone must be one of: professional, casual, academic, creative, persuasive.',
        ];
    }
}
