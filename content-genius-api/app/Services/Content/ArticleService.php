<?php

namespace App\Services\Content;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Jobs\GenerateArticleJob;
use App\Models\ContentGeneration;
use App\Models\User;
use App\Services\CreditService;

class ArticleService
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Create an article generation request.
     */
    public function create(User $user, array $data): ContentGeneration|false
    {
        // Check credits
        if (!$this->creditService->hasEnoughCredits($user, ContentType::ARTICLE)) {
            return false;
        }

        // Deduct credits
        if (!$this->creditService->deductCredits($user, ContentType::ARTICLE)) {
            return false;
        }

        // Create the content generation record
        $contentGeneration = ContentGeneration::create([
            'user_id' => $user->id,
            'type' => ContentType::ARTICLE,
            'status' => ContentStatus::PENDING,
            'input_text' => $data['topic'],
            'input_parameters' => [
                'tone' => $data['tone'] ?? 'professional',
                'word_count' => $data['word_count'] ?? 500,
            ],
            'credits_used' => ContentType::ARTICLE->creditCost(),
        ]);

        // Dispatch the job
        GenerateArticleJob::dispatch($contentGeneration);

        return $contentGeneration;
    }

    /**
     * Get article generation by UUID.
     */
    public function getByUuid(User $user, string $uuid): ?ContentGeneration
    {
        return $user->contentGenerations()
            ->ofType(ContentType::ARTICLE)
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * Get all article generations for a user.
     */
    public function getAllForUser(User $user, int $perPage = 15)
    {
        return $user->contentGenerations()
            ->ofType(ContentType::ARTICLE)
            ->latest()
            ->paginate($perPage);
    }
}
