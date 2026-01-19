<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\SummarizeTextRequest;
use App\Http\Resources\ContentGenerationResource;
use App\Services\Content\SummaryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected SummaryService $summaryService
    ) {}

    /**
     * Create a new text summary request.
     */
    public function store(SummarizeTextRequest $request): JsonResponse
    {
        $contentGeneration = $this->summaryService->create(
            $request->user(),
            $request->validated()
        );

        if (!$contentGeneration) {
            return $this->insufficientCreditsResponse();
        }

        return $this->acceptedResponse(
            new ContentGenerationResource($contentGeneration),
            'Text summary started. Use the UUID to check status.'
        );
    }

    /**
     * Get summary status and result.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $contentGeneration = $this->summaryService->getByUuid(
            $request->user(),
            $uuid
        );

        if (!$contentGeneration) {
            return $this->notFoundResponse('Summary not found.');
        }

        return $this->successResponse(
            new ContentGenerationResource($contentGeneration)
        );
    }

    /**
     * Get all summaries for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $summaries = $this->summaryService->getAllForUser(
            $request->user(),
            $request->input('per_page', 15)
        );

        return $this->successResponse([
            'summaries' => ContentGenerationResource::collection($summaries),
            'pagination' => [
                'current_page' => $summaries->currentPage(),
                'last_page' => $summaries->lastPage(),
                'per_page' => $summaries->perPage(),
                'total' => $summaries->total(),
            ],
        ]);
    }
}
