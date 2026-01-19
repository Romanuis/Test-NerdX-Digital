<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContentGenerationResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentHistoryController extends Controller
{
    use ApiResponse;

    /**
     * Get all content generations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->contentGenerations()->latest();

        // Filter by type if provided
        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min($request->input('per_page', 15), 100);
        $history = $query->paginate($perPage);

        return $this->successResponse([
            'history' => ContentGenerationResource::collection($history),
            'pagination' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
            ],
            'filters' => [
                'type' => $request->input('type'),
                'status' => $request->input('status'),
            ],
        ]);
    }

    /**
     * Get a specific content generation by UUID.
     */
    public function show(Request $request, string $uuid): JsonResponse
    {
        $contentGeneration = $request->user()
            ->contentGenerations()
            ->where('uuid', $uuid)
            ->first();

        if (!$contentGeneration) {
            return $this->notFoundResponse('Content not found.');
        }

        return $this->successResponse(
            new ContentGenerationResource($contentGeneration)
        );
    }

    /**
     * Get usage statistics for the authenticated user.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = $user->contentGenerations()
            ->selectRaw('type, COUNT(*) as count, SUM(credits_used) as credits_spent')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->type->value => [
                        'count' => $item->count,
                        'credits_spent' => $item->credits_spent,
                    ],
                ];
            });

        $totalGenerations = $user->contentGenerations()->count();
        $totalCreditsSpent = $user->contentGenerations()->sum('credits_used');
        $completedGenerations = $user->contentGenerations()->completed()->count();
        $failedGenerations = $user->contentGenerations()->where('status', 'failed')->count();

        return $this->successResponse([
            'overview' => [
                'total_generations' => $totalGenerations,
                'completed' => $completedGenerations,
                'failed' => $failedGenerations,
                'total_credits_spent' => $totalCreditsSpent,
                'current_balance' => $user->credits,
            ],
            'by_type' => $stats,
        ]);
    }
}
