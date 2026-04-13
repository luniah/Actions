<?php

namespace App\Domain\Repositories;

use App\Domain\EloquentModels\Recommendation;
use Illuminate\Database\Eloquent\Collection;

class RecommendationRepository
{
    public function __construct(
        private readonly Recommendation $model = new Recommendation()
    ) {}

    /**
     * Получить все рекомендации
     */
    public function getAll(): Collection
    {
        return $this->model->query()->latest()->get();
    }

    /**
     * Найти рекомендацию по ID
     */
    public function findById(int $id): ?Recommendation
    {
        return $this->model->query()->find($id);
    }

    /**
     * Получить рекомендации для пользователя
     */
    public function getByUser(int $userId, int $limit = 20): Collection
    {
        return $this->model->query()
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Получить последние рекомендации для пользователя
     */
    public function getRecentByUser(int $userId, int $days = 7): Collection
    {
        return $this->model->query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->latest()
            ->get();
    }

    /**
     * Получить рекомендации по типу действия
     */
    public function getByActionType(int $userId, string $actionType, int $limit = 10): Collection
    {
        return $this->model->query()
            ->where('user_id', $userId)
            ->where('action_type', $actionType)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Создать новую рекомендацию
     */
    public function create(array $data): Recommendation
    {
        return $this->model->query()->create($data);
    }

    /**
     * Удалить рекомендацию
     */
    public function delete(int $id): bool
    {
        $recommendation = $this->model->query()->find($id);
        return $recommendation ? $recommendation->delete() : false;
    }

    /**
     * Удалить старые рекомендации пользователя
     */
    public function deleteOldForUser(int $userId, int $keepDays = 30): int
    {
        return $this->model->query()
            ->where('user_id', $userId)
            ->where('created_at', '<', now()->subDays($keepDays))
            ->delete();
    }
}
