<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "GetRecommendationRequest",
    description: "Запрос на получение рекомендации действия",
    required: ["userId"]
)]
class GetRecommendationRequest extends FormRequest
{
    /**
     * Правила валидации
     */
    public function rules(): array
    {
        return [
            'userId' => 'required|integer',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
        ];
    }

    /**
     * Кастомные сообщения об ошибках
     */
    public function messages(): array
    {
        return [
            'userId.required' => 'Необходимо указать ID пользователя',
            'userId.integer' => 'ID пользователя должен быть целым числом',
        ];
    }
}
