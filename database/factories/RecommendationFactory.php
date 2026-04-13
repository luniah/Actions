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
            'action_id' => $this->faker->numberBetween(1, 5),
            'context' => [
                'season' => $this->faker->randomElement(['WINTER', 'SPRING', 'SUMMER', 'AUTUMN']),
                'day_time' => $this->faker->randomElement(['MORNING', 'AFTERNOON', 'EVENING', 'NIGHT']),
                'weather_condition' => $this->faker->randomElement(['CLEAR', 'CLOUDY', 'RAINY', 'SNOWY']),
                'temperature' => $this->faker->numberBetween(-20, 35),
            ],
            'suggestions' => [],
            'created_at' => now(),
            'updated_at' => now(),
        ];
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
        ]);
    }
}
