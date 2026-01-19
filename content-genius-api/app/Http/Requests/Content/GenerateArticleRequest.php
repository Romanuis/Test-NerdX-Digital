<?php

namespace App\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class GenerateArticleRequest extends FormRequest
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
            'topic' => ['required', 'string', 'min:10', 'max:500'],
            'tone' => ['sometimes', 'string', 'in:professional,casual,academic,creative,persuasive'],
            'word_count' => ['sometimes', 'integer', 'min:100', 'max:2000'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'topic.required' => 'Article topic is required.',
            'topic.min' => 'Topic must be at least 10 characters.',
            'topic.max' => 'Topic cannot exceed 500 characters.',
            'tone.in' => 'Tone must be one of: professional, casual, academic, creative, persuasive.',
            'word_count.min' => 'Word count must be at least 100.',
            'word_count.max' => 'Word count cannot exceed 2000.',
        ];
    }
}
