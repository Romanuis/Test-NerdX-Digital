<?php

namespace App\Services\Content;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Jobs\RewriteTextJob;
use App\Models\ContentGeneration;
use App\Models\User;
use App\Services\CreditService;

class RewriteService
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Create a text rewrite request.
     */
    public function create(User $user, array $data): ContentGeneration|false
    {
        // Check credits
        if (!$this->creditService->hasEnoughCredits($user, ContentType::REWRITE)) {
            return false;
        }

        // Deduct credits
        if (!$this->creditService->deductCredits($user, ContentType::REWRITE)) {
            return false;
        }

        // Create the content generation record
        $contentGeneration = ContentGeneration::create([
            'user_id' => $user->id,
            'type' => ContentType::REWRITE,
            'status' => ContentStatus::PENDING,
            'input_text' => $data['text'],
            'input_parameters' => [
                'tone' => $data['tone'] ?? 'professional',
            ],
            'credits_used' => ContentType::REWRITE->creditCost(),
        ]);

        // Dispatch the job
        RewriteTextJob::dispatch($contentGeneration);

        return $contentGeneration;
    }

    /**
     * Get rewrite by UUID.
     */
    public function getByUuid(User $user, string $uuid): ?ContentGeneration
    {
        return $user->contentGenerations()
            ->ofType(ContentType::REWRITE)
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * Get all rewrites for a user.
     */
    public function getAllForUser(User $user, int $perPage = 15)
    {
        return $user->contentGenerations()
            ->ofType(ContentType::REWRITE)
            ->latest()
            ->paginate($perPage);
    }
}
