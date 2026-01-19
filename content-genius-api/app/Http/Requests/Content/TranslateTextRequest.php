<?php

namespace App\Http\Requests\Content;

use App\Services\Content\TranslationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslateTextRequest extends FormRequest
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
        $supportedLanguages = array_keys(TranslationService::SUPPORTED_LANGUAGES);

        return [
            'text' => ['required', 'string', 'min:5', 'max:5000'],
            'target_language' => ['required', 'string', Rule::in($supportedLanguages)],
            'source_language' => ['sometimes', 'string', Rule::in(array_merge(['auto'], $supportedLanguages))],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'text.required' => 'Text to translate is required.',
            'text.min' => 'Text must be at least 5 characters.',
            'text.max' => 'Text cannot exceed 5000 characters.',
            'target_language.required' => 'Target language is required.',
            'target_language.in' => 'Invalid target language. Please check supported languages.',
            'source_language.in' => 'Invalid source language. Use "auto" for auto-detection.',
        ];
    }
}
