<?php

namespace App\Enums;

enum ContentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    /**
     * Check if status allows retry.
     */
    public function canRetry(): bool
    {
        return $this === self::FAILED;
    }

    /**
     * Check if content is ready.
     */
    public function isReady(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
        };
    }
}
