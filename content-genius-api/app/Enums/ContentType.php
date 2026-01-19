<?php

namespace App\Enums;

enum ContentType: string
{
    case ARTICLE = 'article';
    case REWRITE = 'rewrite';
    case SUMMARY = 'summary';
    case EMAIL = 'email';
    case TRANSLATION = 'translation';

    /**
     * Get the credit cost for each content type.
     */
    public function creditCost(): int
    {
        return match ($this) {
            self::ARTICLE => 3,
            self::REWRITE => 2,
            self::SUMMARY => 1,
            self::EMAIL => 2,
            self::TRANSLATION => 2,
        };
    }

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ARTICLE => 'Article Generation',
            self::REWRITE => 'Text Rewriting',
            self::SUMMARY => 'Text Summary',
            self::EMAIL => 'Email Generation',
            self::TRANSLATION => 'Translation',
        };
    }
}
