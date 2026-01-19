<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\CreditService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Get user profile with credit information.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'user' => new UserResource($user),
            'credits' => [
                'balance' => $user->credits,
                'pricing' => $this->creditService->getPricing(),
            ],
        ]);
    }

    /**
     * Update user profile.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->update($validated);

        return $this->successResponse(
            new UserResource($user->fresh()),
            'Profile updated successfully.'
        );
    }

    /**
     * Get credit balance and usage statistics.
     */
    public function credits(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'balance' => $user->credits,
            'total_generations' => $user->total_generations,
            'pricing' => $this->creditService->getPricing(),
        ]);
    }
}
