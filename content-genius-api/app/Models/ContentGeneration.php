<?php

namespace App\Models;

use App\Enums\ContentStatus;
use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ContentGeneration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'status',
        'input_text',
        'input_parameters',
        'output_text',
        'metadata',
        'error_message',
        'retry_count',
        'credits_used',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
            'status' => ContentStatus::class,
            'input_parameters' => 'array',
            'metadata' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the user that owns the content generation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include a specific type.
     */
    public function scopeOfType($query, ContentType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to only include completed generations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', ContentStatus::COMPLETED);
    }

    /**
     * Scope a query to only include pending generations.
     */
    public function scopePending($query)
    {
        return $query->where('status', ContentStatus::PENDING);
    }

    /**
     * Mark the generation as processing.
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => ContentStatus::PROCESSING]);
    }

    /**
     * Mark the generation as completed.
     */
    public function markAsCompleted(string $outputText, array $metadata = []): void
    {
        $this->update([
            'status' => ContentStatus::COMPLETED,
            'output_text' => $outputText,
            'metadata' => $metadata,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark the generation as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => ContentStatus::FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Check if the generation can be retried.
     */
    public function canRetry(): bool
    {
        return $this->status->canRetry() && $this->retry_count < 3;
    }

    /**
     * Check if the generation is ready.
     */
    public function isReady(): bool
    {
        return $this->status->isReady();
    }
}
