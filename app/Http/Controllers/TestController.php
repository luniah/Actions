<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Test",
    description: "Тестовые эндпоинты"
)]
class TestController extends Controller
{
    #[OA\Get(
        path: "/api/test",
        description: "Возвращает приветственное сообщение",
        summary: "Тестовый эндпоинт",
        tags: ["Test"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный ответ",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Hello World!"
                        )
                    ]
                )
            )
        ]
    )]
    public function test()
    {
        return response()->json(['message' => 'Hello World!']);
    }
}
