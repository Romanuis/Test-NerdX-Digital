<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\TranslateTextRequest;
use App\Http\Resources\ContentGenerationResource;
use App\Services\Content\TranslationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected TranslationService $translationService
    ) {}

    /**
     * Create a new translation request.
     */
    public function store(TranslateTextRequest $request): JsonResponse
    {
        $contentGeneration = $this->translationService->create(
            $request->user(),
            $request->validated()
        );

        if (!$contentGeneration) {
            return $this->insufficientCreditsResponse();
        }

        return $this->acceptedResponse(
            new ContentGenerationResource($contentGeneration),
            'Translation started. Use the UUID to check status.'
        );
    }

    /**
     * Get translation status and result.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $contentGeneration = $this->translationService->getByUuid(
            $request->user(),
            $uuid
        );

        if (!$contentGeneration) {
            return $this->notFoundResponse('Translation not found.');
        }

        return $this->successResponse(
            new ContentGenerationResource($contentGeneration)
        );
    }

    /**
     * Get all translations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $translations = $this->translationService->getAllForUser(
            $request->user(),
            $request->input('per_page', 15)
        );

        return $this->successResponse([
            'translations' => ContentGenerationResource::collection($translations),
            'pagination' => [
                'current_page' => $translations->currentPage(),
                'last_page' => $translations->lastPage(),
                'per_page' => $translations->perPage(),
                'total' => $translations->total(),
            ],
        ]);
    }

    /**
     * Get supported languages.
     */
    public function languages(): JsonResponse
    {
        return $this->successResponse([
            'languages' => $this->translationService->getSupportedLanguages(),
        ]);
    }
}
