<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContentGenerationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'input' => [
                'text' => $this->input_text,
                'parameters' => $this->input_parameters,
            ],
            'output' => $this->when($this->status->isReady(), [
                'text' => $this->output_text,
            ]),
            'error' => $this->when($this->status->value === 'failed', [
                'message' => $this->error_message,
                'can_retry' => $this->canRetry(),
            ]),
            'credits_used' => $this->credits_used,
            'metadata' => $this->when($this->status->isReady(), $this->metadata),
            'processed_at' => $this->processed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
