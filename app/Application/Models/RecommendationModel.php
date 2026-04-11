<?php

namespace App\Application\Models;

class RecommendationModel
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly string $actionType,
        public readonly ?int $actionId,
        public readonly array $context,
        public readonly array $suggestions,
        public readonly string $createdAt,
    ) {}

    /**
     * Создать DTO из сущности Eloquent
     */
    public static function fromEntity(\App\Domain\EloquentModels\Recommendation $recommendation): self
    {
        return new self(
            id: $recommendation->id,
            userId: $recommendation->user_id,
            actionType: $recommendation->action_type,
            actionId: $recommendation->action_id,
            context: $recommendation->context ?? [],
            suggestions: $recommendation->suggestions ?? [],
            createdAt: $recommendation->created_at->toIso8601String(),
        );
    }

    /**
     * Преобразовать DTO в массив
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'action_type' => $this->actionType,
            'action_id' => $this->actionId,
            'context' => $this->context,
            'suggestions' => $this->suggestions,
            'created_at' => $this->createdAt,
        ];
    }
}
