<?php

namespace App\Application\Models;

class WorldContextModel
{
    public function __construct(
        public readonly string $userId,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly string $dayTime,
        public readonly string $season,
        public readonly array $weather,
        public readonly array $entertainment,
        public readonly string $sunrise,
        public readonly string $sunset,
        public readonly string $updatedAt,
    ) {}

    /**
     * Создать DTO из массива данных от World API
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['userId'],
            latitude: $data['latitude'] ?? null,
            longitude: $data['longitude'] ?? null,
            dayTime: $data['dayTime'],
            season: $data['season'],
            weather: $data['weather'],
            entertainment: $data['entertainment'] ?? [],
            sunrise: $data['sunrise'],
            sunset: $data['sunset'],
            updatedAt: $data['updatedAt'],
        );
    }
}
