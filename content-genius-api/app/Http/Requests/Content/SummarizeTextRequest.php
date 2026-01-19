<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class SummarizeTextRequest extends FormRequest
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
            'text' => ['required', 'string', 'min:100', 'max:10000'],
            'format' => ['sometimes', 'string', 'in:bullets,paragraph,executive'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'text.required' => 'Text to summarize is required.',
            'text.min' => 'Text must be at least 100 characters.',
            'text.max' => 'Text cannot exceed 10000 characters.',
            'format.in' => 'Format must be one of: bullets, paragraph, executive.',
        ];
    }
}
