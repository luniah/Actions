<?php

namespace App\Application\Models;

class MovieModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly array $genres,
        public readonly ?int $releaseYear,
        public readonly ?int $duration,
        public readonly ?string $director,
        public readonly ?string $posterUrl,
        public readonly ?string $externalId,
        public readonly ?array $metadata,
    ) {}

    /**
     * Создать DTO из сущности Eloquent
     */
    public static function fromEntity(\App\Domain\EloquentModels\Movie $movie): self
    {
        return new self(
            id: $movie->id,
            title: $movie->title,
            description: $movie->description,
            genres: $movie->genres ?? [],
            releaseYear: $movie->release_year,
            duration: $movie->duration,
            director: $movie->director,
            posterUrl: $movie->poster_url,
            externalId: $movie->external_id,
            metadata: $movie->metadata,
        );
    }

    /**
     * Преобразовать DTO в массив
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'genres' => $this->genres,
            'release_year' => $this->releaseYear,
            'duration' => $this->duration,
            'director' => $this->director,
            'poster_url' => $this->posterUrl,
            'external_id' => $this->externalId,
            'metadata' => $this->metadata,
        ];
    }
}
