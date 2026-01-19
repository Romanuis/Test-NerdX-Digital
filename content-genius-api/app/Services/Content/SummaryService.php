<?php

namespace App\Services\Content;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Jobs\SummarizeTextJob;
use App\Models\ContentGeneration;
use App\Models\User;
use App\Services\CreditService;

class SummaryService
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Create a text summary request.
     */
    public function create(User $user, array $data): ContentGeneration|false
    {
        // Check credits
        if (!$this->creditService->hasEnoughCredits($user, ContentType::SUMMARY)) {
            return false;
        }

        // Deduct credits
        if (!$this->creditService->deductCredits($user, ContentType::SUMMARY)) {
            return false;
        }

        // Create the content generation record
        $contentGeneration = ContentGeneration::create([
            'user_id' => $user->id,
            'type' => ContentType::SUMMARY,
            'status' => ContentStatus::PENDING,
            'input_text' => $data['text'],
            'input_parameters' => [
                'format' => $data['format'] ?? 'bullets',
            ],
            'credits_used' => ContentType::SUMMARY->creditCost(),
        ]);

        // Dispatch the job
        SummarizeTextJob::dispatch($contentGeneration);

        return $contentGeneration;
    }

    /**
     * Get summary by UUID.
     */
    public function getByUuid(User $user, string $uuid): ?ContentGeneration
    {
        return $user->contentGenerations()
            ->ofType(ContentType::SUMMARY)
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * Get all summaries for a user.
     */
    public function getAllForUser(User $user, int $perPage = 15)
    {
        return $user->contentGenerations()
            ->ofType(ContentType::SUMMARY)
            ->latest()
            ->paginate($perPage);
    }
}
