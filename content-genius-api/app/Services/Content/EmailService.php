<?php

namespace App\Services\Content;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use App\Jobs\GenerateEmailJob;
use App\Models\ContentGeneration;
use App\Models\User;
use App\Services\CreditService;

class EmailService
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Create an email generation request.
     */
    public function create(User $user, array $data): ContentGeneration|false
    {
        // Check credits
        if (!$this->creditService->hasEnoughCredits($user, ContentType::EMAIL)) {
            return false;
        }

        // Deduct credits
        if (!$this->creditService->deductCredits($user, ContentType::EMAIL)) {
            return false;
        }

        // Create the content generation record
        $contentGeneration = ContentGeneration::create([
            'user_id' => $user->id,
            'type' => ContentType::EMAIL,
            'status' => ContentStatus::PENDING,
            'input_text' => $data['purpose'],
            'input_parameters' => [
                'tone' => $data['tone'] ?? 'professional',
                'recipient_name' => $data['recipient_name'] ?? null,
                'sender_name' => $data['sender_name'] ?? null,
                'additional_info' => $data['additional_info'] ?? null,
            ],
            'credits_used' => ContentType::EMAIL->creditCost(),
        ]);

        // Dispatch the job
        GenerateEmailJob::dispatch($contentGeneration);

        return $contentGeneration;
    }

    /**
     * Get email by UUID.
     */
    public function getByUuid(User $user, string $uuid): ?ContentGeneration
    {
        return $user->contentGenerations()
            ->ofType(ContentType::EMAIL)
            ->where('uuid', $uuid)
            ->first();
    }

    /**
     * Get all emails for a user.
     */
    public function getAllForUser(User $user, int $perPage = 15)
    {
        return $user->contentGenerations()
            ->ofType(ContentType::EMAIL)
            ->latest()
            ->paginate($perPage);
    }
}
