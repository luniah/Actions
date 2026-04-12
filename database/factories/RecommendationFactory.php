<?php

namespace Database\Factories;

use App\Domain\EloquentModels\Recommendation;
use App\Domain\EloquentModels\Action;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecommendationFactory extends Factory
{
    protected $model = Recommendation::class;

    /**
     * Определение стандартных значений для модели
     */
    public function definition(): array
    {
        $actionTypes = [
            Action::TYPE_WALK,
            Action::TYPE_SLEEP,
            Action::TYPE_WATCH_MOVIE,
            Action::TYPE_LISTEN_MUSIC,
            Action::TYPE_VISIT_PLACE,
        ];

        $actionType = $this->faker->randomElement($actionTypes);

        return [
            'user_id' => $this->faker->numberBetween(1, 1000),
            'action_type' => $actionType,
            'action_id' => Action::factory(),
            'context' => [
                'season' => $this->faker->randomElement(['WINTER', 'SPRING', 'SUMMER', 'AUTUMN']),
                'day_time' => $this->faker->randomElement(['MORNING', 'AFTERNOON', 'EVENING', 'NIGHT']),
                'weather_condition' => $this->faker->randomElement(['CLEAR', 'CLOUDY', 'RAINY', 'SNOWY']),
                'temperature' => $this->faker->numberBetween(-20, 35),
            ],
            'suggestions' => $this->generateSuggestions($actionType),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Сгенерировать предложения в зависимости от типа действия
     */
    private function generateSuggestions(string $actionType): array
    {
        return match ($actionType) {
            Action::TYPE_WATCH_MOVIE => [
                [
                    'type' => 'movie',
                    'id' => 'movie_' . $this->faker->numberBetween(1, 30),
                    'title' => $this->faker->words(3, true),
                    'reason' => $this->faker->sentence(),
                ],
            ],
            Action::TYPE_LISTEN_MUSIC => [
                [
                    'type' => 'music',
                    'id' => 'music_' . $this->faker->numberBetween(1, 30),
                    'name' => $this->faker->words(2, true),
                    'artist' => $this->faker->name(),
                    'reason' => $this->faker->sentence(),
                ],
            ],
            Action::TYPE_VISIT_PLACE => [
                [
                    'type' => 'place',
                    'id' => $this->faker->numberBetween(1, 10),
                    'name' => $this->faker->company(),
                    'category' => $this->faker->randomElement(['park', 'restaurant', 'cinema', 'museum']),
                    'reason' => 'Находится рядом с вами',
                ],
            ],
            default => [],
        };
    }

    /**
     * Для конкретного пользователя
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Для конкретного типа действия
     */
    public function ofType(string $actionType): static
    {
        return $this->state(fn(array $attributes) => [
            'action_type' => $actionType,
            'suggestions' => $this->generateSuggestions($actionType),
        ]);
    }
}
