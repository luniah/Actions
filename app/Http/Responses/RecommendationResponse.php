<?php

namespace App\Http\Responses;

use App\Application\Models\RecommendationModel;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class RecommendationResponse implements Responsable
{
    public function __construct(
        private readonly RecommendationModel $recommendation
    ) {}

    /**
     * Преобразовать DTO в HTTP ответ
     */
    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'recommendation' => $this->recommendation->toArray()
        ]);
    }
}
