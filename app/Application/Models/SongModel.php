<?php

namespace App\Application\Models;

class SongModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $artist,
        public readonly ?string $album,
        public readonly ?int $duration,
        public readonly array $tags,
        public readonly ?string $externalId,
        public readonly ?array $metadata,
    ) {}

    /**
     * Создать DTO из сущности Eloquent
     */
    public static function fromEntity(\App\Domain\EloquentModels\Song $song): self
    {
        return new self(
            id: $song->id,
            name: $song->name,
            artist: $song->artist,
            album: $song->album,
            duration: $song->duration,
            tags: $song->tags ?? [],
            externalId: $song->external_id,
            metadata: $song->metadata,
        );
    }

    /**
     * Преобразовать DTO в массив
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'artist' => $this->artist,
            'album' => $this->album,
            'duration' => $this->duration,
            'tags' => $this->tags,
            'external_id' => $this->externalId,
            'metadata' => $this->metadata,
        ];
    }
}
