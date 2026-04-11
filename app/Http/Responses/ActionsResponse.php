<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class ActionsResponse implements Responsable
{
    public function __construct(
        private readonly array $actions
    ) {}

    /**
     * Преобразовать в HTTP ответ
     */
    public function toResponse($request = null): JsonResponse
    {
        return response()->json([
            'actions' => $this->actions
        ]);
    }
}
