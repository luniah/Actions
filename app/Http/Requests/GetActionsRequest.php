<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "GetActionsRequest",
    description: "Запрос на получение истории действий",
    required: ["userId"]
)]
class GetActionsRequest extends FormRequest
{
    /**
     * Правила валидации
     */
    public function rules(): array
    {
        return [
            'userId' => 'required|integer',
            'limit' => 'sometimes|integer|min:1|max:100',
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
