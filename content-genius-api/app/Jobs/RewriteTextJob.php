<?php

namespace App\Jobs;

use App\Models\ContentGeneration;
use App\Services\OpenAI\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RewriteTextJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ContentGeneration $contentGeneration
    ) {}

    /**
     * Execute the job.
     */
    public function handle(OpenAIService $openAIService): void
    {
        // Mark as processing
        $this->contentGeneration->markAsProcessing();

        try {
            $params = $this->contentGeneration->input_parameters;

            $result = $openAIService->rewriteText(
                text: $this->contentGeneration->input_text,
                tone: $params['tone'] ?? 'professional'
            );

            if ($result['success']) {
                $this->contentGeneration->markAsCompleted(
                    outputText: $result['content'],
                    metadata: [
                        'model' => $result['model'],
                        'usage' => $result['usage'],
                        'generated_at' => now()->toIso8601String(),
                    ]
                );

                Log::info('Text rewrite completed successfully', [
                    'content_id' => $this->contentGeneration->id,
                    'uuid' => $this->contentGeneration->uuid,
                ]);
            } else {
                $this->handleFailure($result['error'] ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            $this->handleFailure($e->getMessage());
        }
    }

    /**
     * Handle job failure.
     */
    protected function handleFailure(string $errorMessage): void
    {
        Log::error('Text rewrite failed', [
            'content_id' => $this->contentGeneration->id,
            'uuid' => $this->contentGeneration->uuid,
            'error' => $errorMessage,
        ]);

        $this->contentGeneration->markAsFailed($errorMessage);
    }

    /**
     * Handle a job failure after all retries.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('Text rewrite job failed permanently', [
            'content_id' => $this->contentGeneration->id,
            'uuid' => $this->contentGeneration->uuid,
            'error' => $exception->getMessage(),
        ]);

        $this->contentGeneration->markAsFailed('Job failed permanently: ' . $exception->getMessage());
    }
}
