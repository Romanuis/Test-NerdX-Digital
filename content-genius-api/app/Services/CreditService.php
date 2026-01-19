<?php

namespace App\Services;

use App\Enums\ContentType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreditService
{
    /**
     * Check if user has sufficient credits for a content type.
     */
    public function hasEnoughCredits(User $user, ContentType $contentType): bool
    {
        return $user->hasCredits($contentType->creditCost());
    }

    /**
     * Get the credit cost for a content type.
     */
    public function getCreditCost(ContentType $contentType): int
    {
        return $contentType->creditCost();
    }

    /**
     * Deduct credits for a content generation.
     */
    public function deductCredits(User $user, ContentType $contentType): bool
    {
        return DB::transaction(function () use ($user, $contentType) {
            // Refresh user to get latest credit balance
            $user->refresh();

            $cost = $contentType->creditCost();

            if (!$user->hasCredits($cost)) {
                return false;
            }

            return $user->deductCredits($cost);
        });
    }

    /**
     * Refund credits to user (in case of failure).
     */
    public function refundCredits(User $user, ContentType $contentType): void
    {
        $user->addCredits($contentType->creditCost());
    }

    /**
     * Get user's credit balance.
     */
    public function getBalance(User $user): int
    {
        return $user->credits;
    }

    /**
     * Get credit pricing information.
     */
    public function getPricing(): array
    {
        return collect(ContentType::cases())->mapWithKeys(function (ContentType $type) {
            return [
                $type->value => [
                    'type' => $type->value,
                    'label' => $type->label(),
                    'credits' => $type->creditCost(),
                ],
            ];
        })->toArray();
    }
}
