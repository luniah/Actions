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
        public readonly array $mood,
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
            mood: $song->mood ?? [],
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
            'mood' => $this->mood,
            'external_id' => $this->externalId,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Проверить наличие настроения
     */
    public function hasMood(string $mood): bool
    {
        return in_array($mood, $this->mood);
    }

    /**
     * Получить все настроения трека
     */
    public function getMoods(): array
    {
        return $this->mood;
    }

    /**
     * Получить основной жанр из метаданных
     */
    public function getGenre(): ?string
    {
        return $this->metadata['genre'] ?? null;
    }

    /**
     * Получить год выпуска из метаданных
     */
    public function getReleaseYear(): ?int
    {
        return $this->metadata['release_year'] ?? null;
    }

    /**
     * Получить популярность из метаданных
     */
    public function getPopularity(): ?int
    {
        return $this->metadata['popularity'] ?? null;
    }

    /**
     * Получить источник данных
     */
    public function getSource(): ?string
    {
        return $this->metadata['source'] ?? null;
    }
}
