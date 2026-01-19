<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\GenerateEmailRequest;
use App\Http\Resources\ContentGenerationResource;
use App\Services\Content\EmailService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected EmailService $emailService
    ) {}

    /**
     * Generate a new email.
     */
    public function store(GenerateEmailRequest $request): JsonResponse
    {
        $contentGeneration = $this->emailService->create(
            $request->user(),
            $request->validated()
        );

        if (!$contentGeneration) {
            return $this->insufficientCreditsResponse();
        }

        return $this->acceptedResponse(
            new ContentGenerationResource($contentGeneration),
            'Email generation started. Use the UUID to check status.'
        );
    }

    /**
     * Get email generation status and result.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $contentGeneration = $this->emailService->getByUuid(
            $request->user(),
            $uuid
        );

        if (!$contentGeneration) {
            return $this->notFoundResponse('Email not found.');
        }

        return $this->successResponse(
            new ContentGenerationResource($contentGeneration)
        );
    }

    /**
     * Get all emails for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $emails = $this->emailService->getAllForUser(
            $request->user(),
            $request->input('per_page', 15)
        );

        return $this->successResponse([
            'emails' => ContentGenerationResource::collection($emails),
            'pagination' => [
                'current_page' => $emails->currentPage(),
                'last_page' => $emails->lastPage(),
                'per_page' => $emails->perPage(),
                'total' => $emails->total(),
            ],
        ]);
    }
}
