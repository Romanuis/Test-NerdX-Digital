<?php

namespace App\Services\Content;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Jobs\TranslateTextJob;
use App\Models\ContentGeneration;
use App\Models\User;
use App\Services\CreditService;

class TranslationService
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Supported languages for translation.
     */
    public const SUPPORTED_LANGUAGES = [
        'en' => 'English',
        'fr' => 'French',
        'es' => 'Spanish',
        'de' => 'German',
        'it' => 'Italian',
        'pt' => 'Portuguese',
        'nl' => 'Dutch',
        'ru' => 'Russian',
        'zh' => 'Chinese',
        'ja' => 'Japanese',
        'ko' => 'Korean',
        'ar' => 'Arabic',
    ];

    /**
     * Create a translation request.
     */
    public function create(User $user, array $data): ContentGeneration|false
    {
        // Check credits
        if (!$this->creditService->hasEnoughCredits($user, ContentType::TRANSLATION)) {
            return false;
        }

        // Deduct credits
        if (!$this->creditService->deductCredits($user, ContentType::TRANSLATION)) {
            return false;
        }

        // Create the content generation record
        $contentGeneration = ContentGeneration::create([
            'user_id' => $user->id,
            'type' => ContentType::TRANSLATION,
            'status' => ContentStatus::PENDING,
            'input_text' => $data['text'],
            'input_parameters' => [
                'source_language' => $data['source_language'] ?? 'auto',
                'target_language' => $data['target_language'],
            ],
            'credits_used' => ContentType::TRANSLATION->creditCost(),
        ]);

        // Dispatch the job
        TranslateTextJob::dispatch($contentGeneration);

        return $contentGeneration;
    }

    /**
     * Get translation by UUID.
     */
    public function getByUuid(User $user, string $uuid): ?ContentGeneration
    {
        return $user->contentGenerations()
            ->ofType(ContentType::TRANSLATION)
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * Get all translations for a user.
     */
    public function getAllForUser(User $user, int $perPage = 15)
    {
        return $user->contentGenerations()
            ->ofType(ContentType::TRANSLATION)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get supported languages.
     */
    public function getSupportedLanguages(): array
    {
        return self::SUPPORTED_LANGUAGES;
    }

    /**
     * Check if a language is supported.
     */
    public function isLanguageSupported(string $code): bool
    {
        return isset(self::SUPPORTED_LANGUAGES[$code]) || $code === 'auto';
    }
}
