<?php

namespace Database\Factories;

use App\Domain\EloquentModels\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;

class MovieFactory extends Factory
{
    protected $model = Movie::class;

    /**
     * Определение стандартных значений для модели
     */
    public function definition(): array
    {
        $genresList = [
            'драма', 'комедия', 'боевик', 'фантастика', 'ужасы',
            'мелодрама', 'триллер', 'приключения', 'криминал', 'семейный',
        ];

        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'genres' => $this->faker->randomElements($genresList, $this->faker->numberBetween(1, 3)),
            'release_year' => $this->faker->year(),
            'duration' => $this->faker->numberBetween(80, 200),
            'director' => $this->faker->name(),
            'poster_url' => $this->faker->imageUrl(),
            'external_id' => $this->faker->uuid(),
            'metadata' => [
                'rating' => $this->faker->randomFloat(1, 5, 9.5),
                'mood' => $this->faker->randomElements(
                    ['epic', 'funny', 'dark', 'inspiring', 'romantic', 'tense'],
                    $this->faker->numberBetween(1, 3)
                ),
            ],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Состояние для драмы
     */
    public function drama(): static
    {
        return $this->state(fn(array $attributes) => [
            'genres' => ['драма'],
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'mood' => ['emotional', 'serious'],
            ]),
        ]);
    }

    /**
     * Состояние для комедии
     */
    public function comedy(): static
    {
        return $this->state(fn(array $attributes) => [
            'genres' => ['комедия'],
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'mood' => ['funny', 'light'],
            ]),
        ]);
    }

    /**
     * Состояние с высоким рейтингом
     */
    public function topRated(): static
    {
        return $this->state(fn(array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'rating' => $this->faker->randomFloat(1, 8.5, 9.9),
            ]),
        ]);
    }
}
