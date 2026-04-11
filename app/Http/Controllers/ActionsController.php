<?php

namespace App\Http\Controllers;

use App\Application\Services\RecommendationService;
use App\Application\Services\WorldServiceClient;
use App\Http\Controllers\Base\Controller;
use App\Http\Requests\GetRecommendationRequest;
use App\Http\Requests\GetActionsRequest;
use App\Http\Responses\RecommendationResponse;
use App\Http\Responses\ActionsResponse;
use App\Domain\Repositories\ActionRepository;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Actions",
    description: "Получение доступных действий и рекомендаций"
)]
class ActionsController extends Controller
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
        private readonly WorldServiceClient $worldServiceClient,
        private readonly ActionRepository $actionRepository,
    ) {}

    #[OA\Post(
        path: "/api/v1/actions/recommend",
        description: "Возвращает рекомендованное действие для пользователя на основе текущего контекста окружающего мира",
        summary: "Получить рекомендацию действия",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["userId"],
                properties: [
                    new OA\Property(
                        property: "userId",
                        description: "ID пользователя",
                        type: "integer",
                        example: 123
                    ),
                    new OA\Property(
                        property: "latitude",
                        description: "Широта",
                        type: "number",
                        format: "float",
                        example: 55.7558
                    ),
                    new OA\Property(
                        property: "longitude",
                        description: "Долгота",
                        type: "number",
                        format: "float",
                        example: 37.6176
                    )
                ]
            )
        ),
        tags: ["Actions"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный ответ с рекомендацией",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "recommendation",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "user_id", type: "integer", example: 123),
                                new OA\Property(property: "action_type", type: "string", example: "watch_movie"),
                                new OA\Property(property: "action_id", type: "integer", example: 3),
                                new OA\Property(
                                    property: "context",
                                    type: "object",
                                    example: [
                                        "season" => "WINTER",
                                        "day_time" => "EVENING",
                                        "weather_condition" => "SNOWY",
                                        "temperature" => -5
                                    ]
                                ),
                                new OA\Property(
                                    property: "suggestions",
                                    type: "array",
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "type", type: "string", example: "movie"),
                                            new OA\Property(property: "id", type: "integer", example: 1),
                                            new OA\Property(property: "title", type: "string", example: "Один дома"),
                                            new OA\Property(property: "reason", type: "string", example: "Подходит под жанр: семейный")
                                        ],
                                        type: "object"
                                    )
                                ),
                                new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-01-20T15:30:00Z")
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Ошибка валидации"
            ),
            new OA\Response(
                response: 500,
                description: "Внутренняя ошибка сервера"
            )
        ]
    )]
    public function recommend(GetRecommendationRequest $request): JsonResponse
    {
        try {
            // Получаем контекст из сервиса World
            $worldContext = $this->worldServiceClient->getWorldContext(
                $request->userId,
                $request->latitude,
                $request->longitude
            );

            // Получаем рекомендацию
            $recommendation = $this->recommendationService->recommend($worldContext);

            return (new RecommendationResponse($recommendation))->toResponse($request);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get recommendation',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/v1/actions",
        description: "Возвращает список всех доступных типов действий",
        summary: "Получить список доступных действий",
        tags: ["Actions"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список доступных действий",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "actions",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "type", type: "string", example: "walk"),
                                    new OA\Property(property: "name", type: "string", example: "Прогулка"),
                                    new OA\Property(property: "description", type: "string", example: "Прогулка на свежем воздухе"),
                                    new OA\Property(property: "metadata", type: "object", example: ["icon" => "walk"])
                                ],
                                type: "object"
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Внутренняя ошибка сервера"
            )
        ]
    )]
    public function index(): JsonResponse
    {
        try {
            $actions = $this->actionRepository->getAll();

            $actionsArray = $actions->map(function ($action) {
                return \App\Application\Models\ActionModel::fromEntity($action)->toArray();
            })->toArray();

            return (new ActionsResponse($actionsArray))->toResponse();

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get actions',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: "/api/v1/actions/history",
        description: "Возвращает историю рекомендаций для пользователя",
        summary: "Получить историю рекомендаций",
        tags: ["Actions"],
        parameters: [
            new OA\Parameter(
                name: "userId",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "integer"),
                example: 123
            ),
            new OA\Parameter(
                name: "limit",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 20),
                example: 10
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "История рекомендаций пользователя",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "history",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "user_id", type: "integer"),
                                    new OA\Property(property: "action_type", type: "string"),
                                    new OA\Property(
                                        property: "suggestions",
                                        type: "array",
                                        items: new OA\Items(
                                            properties: [
                                                new OA\Property(property: "type", type: "string"),
                                                new OA\Property(property: "id", type: "integer"),
                                                new OA\Property(property: "title", type: "string"),
                                            ],
                                            type: "object"
                                        )
                                    ),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time")
                                ],
                                type: "object"
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Ошибка валидации"
            ),
            new OA\Response(
                response: 500,
                description: "Внутренняя ошибка сервера"
            )
        ]
    )]
    public function history(GetActionsRequest $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 20);
            $history = $this->recommendationService->getUserHistory($request->userId, $limit);

            return response()->json([
                'history' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get history',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
