<?php

namespace App\Application\Models;

class ActionModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $type,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?array $metadata,
    ) {}

    /**
     * Создать DTO из сущности Eloquent
     */
    public static function fromEntity(\App\Domain\EloquentModels\Action $action): self
    {
        return new self(
            id: $action->id,
            type: $action->type,
            name: $action->name,
            description: $action->description,
            metadata: $action->metadata,
        );
    }

    /**
     * Преобразовать DTO в массив
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'metadata' => $this->metadata,
        ];
    }
}
