<?php

namespace Database\Factories;

use App\Domain\EloquentModels\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

class SongFactory extends Factory
{
    protected $model = Song::class;

    /**
     * Определение стандартных значений для модели
     */
    public function definition(): array
    {
        $moods = [
            ['happy', 'energetic'],
            ['calm', 'peaceful'],
            ['sad', 'melancholic'],
            ['dance', 'funky'],
            ['romantic', 'emotional'],
        ];

        return [
            'name' => $this->faker->words(3, true),
            'artist' => $this->faker->name(),
            'album' => $this->faker->words(2, true),
            'duration' => $this->faker->numberBetween(120, 400),
            'mood' => $this->faker->randomElement($moods),
            'external_id' => $this->faker->uuid(),
            'metadata' => [
                'genre' => $this->faker->randomElement(['pop', 'rock', 'jazz', 'classical', 'electronic']),
                'release_year' => $this->faker->year(),
                'popularity' => $this->faker->numberBetween(0, 100),
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Состояние для энергичного трека
     */
    public function energetic(): static
    {
        return $this->state(fn(array $attributes) => [
            'mood' => ['energetic', 'happy'],
        ]);
    }

    /**
     * Состояние для спокойного трека
     */
    public function calm(): static
    {
        return $this->state(fn(array $attributes) => [
            'mood' => ['calm', 'peaceful'],
        ]);
    }
}
