<?php

namespace Database\Factories;

use App\Domain\EloquentModels\Action;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActionFactory extends Factory
{
    protected $model = Action::class;

    /**
     * Определение стандартных значений для модели
     */
    public function definition(): array
    {
        $types = [
            Action::TYPE_WALK => 'Прогулка',
            Action::TYPE_SLEEP => 'Сон',
            Action::TYPE_WATCH_MOVIE => 'Просмотр фильма',
            Action::TYPE_LISTEN_MUSIC => 'Прослушивание музыки',
            Action::TYPE_VISIT_PLACE => 'Посещение места',
        ];

        $type = $this->faker->randomElement(array_keys($types));
        $icons = [
            Action::TYPE_WALK => '🚶',
            Action::TYPE_SLEEP => '😴',
            Action::TYPE_WATCH_MOVIE => '🎬',
            Action::TYPE_LISTEN_MUSIC => '🎵',
            Action::TYPE_VISIT_PLACE => '📍',
        ];

        return [
            'type' => $type,
            'name' => $types[$type],
            'description' => $this->faker->sentence(),
            'metadata' => ['icon' => $icons[$type]],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Состояние для конкретного типа действия
     */
    public function walk(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Action::TYPE_WALK,
            'name' => 'Прогулка',
            'metadata' => ['icon' => '🚶'],
        ]);
    }

    public function sleep(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Action::TYPE_SLEEP,
            'name' => 'Сон',
            'metadata' => ['icon' => '😴'],
        ]);
    }

    public function watchMovie(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Action::TYPE_WATCH_MOVIE,
            'name' => 'Просмотр фильма',
            'metadata' => ['icon' => '🎬'],
        ]);
    }

    public function listenMusic(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Action::TYPE_LISTEN_MUSIC,
            'name' => 'Прослушивание музыки',
            'metadata' => ['icon' => '🎵'],
        ]);
    }

    public function visitPlace(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => Action::TYPE_VISIT_PLACE,
            'name' => 'Посещение места',
            'metadata' => ['icon' => '📍'],
        ]);
    }
}
