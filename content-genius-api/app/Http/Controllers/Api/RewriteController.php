<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\RewriteTextRequest;
use App\Http\Resources\ContentGenerationResource;
use App\Services\Content\RewriteService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RewriteController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected RewriteService $rewriteService
    ) {}

    /**
     * Create a new text rewrite request.
     */
    public function store(RewriteTextRequest $request): JsonResponse
    {
        $contentGeneration = $this->rewriteService->create(
            $request->user(),
            $request->validated()
        );

        if (!$contentGeneration) {
            return $this->insufficientCreditsResponse();
        }

        return $this->acceptedResponse(
            new ContentGenerationResource($contentGeneration),
            'Text rewrite started. Use the UUID to check status.'
        );
    }

    /**
     * Get rewrite status and result.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $contentGeneration = $this->rewriteService->getByUuid(
            $request->user(),
            $uuid
        );

        if (!$contentGeneration) {
            return $this->notFoundResponse('Rewrite not found.');
        }

        return $this->successResponse(
            new ContentGenerationResource($contentGeneration)
        );
    }

    /**
     * Get all rewrites for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $rewrites = $this->rewriteService->getAllForUser(
            $request->user(),
            $request->input('per_page', 15)
        );

        return $this->successResponse([
            'rewrites' => ContentGenerationResource::collection($rewrites),
            'pagination' => [
                'current_page' => $rewrites->currentPage(),
                'last_page' => $rewrites->lastPage(),
                'per_page' => $rewrites->perPage(),
                'total' => $rewrites->total(),
            ],
        ]);
    }
}
