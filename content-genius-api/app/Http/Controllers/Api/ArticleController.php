<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Content\GenerateArticleRequest;
use App\Http\Resources\ContentGenerationResource;
use App\Services\Content\ArticleService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected ArticleService $articleService
    ) {}

    /**
     * Generate a new article.
     */
    public function store(GenerateArticleRequest $request): JsonResponse
    {
        $contentGeneration = $this->articleService->create(
            $request->user(),
            $request->validated()
        );

        if (!$contentGeneration) {
            return $this->insufficientCreditsResponse();
        }

        return $this->acceptedResponse(
            new ContentGenerationResource($contentGeneration),
            'Article generation started. Use the UUID to check status.'
        );
    }

    /**
     * Get article generation status and result.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $contentGeneration = $this->articleService->getByUuid(
            $request->user(),
            $uuid
        );

        if (!$contentGeneration) {
            return $this->notFoundResponse('Article not found.');
        }

        return $this->successResponse(
            new ContentGenerationResource($contentGeneration)
        );
    }

    /**
     * Get all articles for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $articles = $this->articleService->getAllForUser(
            $request->user(),
            $request->input('per_page', 15)
        );

        return $this->successResponse([
            'articles' => ContentGenerationResource::collection($articles),
            'pagination' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }
}
