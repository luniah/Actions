<?php

namespace App\Domain\Repositories;

use App\Domain\EloquentModels\Recommendation;
use Illuminate\Database\Eloquent\Collection;

class RecommendationRepository
{
    /**
     * Получить все рекомендации
     */
    public function getAll(): Collection
    {
        return Recommendation::latest()->get();
    }

    /**
     * Найти рекомендацию по ID
     */
    public function findById(int $id): ?Recommendation
    {
        return Recommendation::find($id);
    }

    /**
     * Получить рекомендации для пользователя
     */
    public function getByUser(int $userId, int $limit = 20): Collection
    {
        return Recommendation::byUser($userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Получить последние рекомендации для пользователя
     */
    public function getRecentByUser(int $userId, int $days = 7): Collection
    {
        return Recommendation::byUser($userId)
            ->recent($days)
            ->latest()
            ->get();
    }

    /**
     * Получить рекомендации по типу действия
     */
    public function getByActionType(int $userId, string $actionType, int $limit = 10): Collection
    {
        return Recommendation::byUser($userId)
            ->byActionType($actionType)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Создать новую рекомендацию
     */
    public function create(array $data): Recommendation
    {
        return Recommendation::create($data);
    }

    /**
     * Удалить рекомендацию
     */
    public function delete(int $id): bool
    {
        $recommendation = Recommendation::find($id);
        return $recommendation ? $recommendation->delete() : false;
    }

    /**
     * Удалить старые рекомендации пользователя
     */
    public function deleteOldForUser(int $userId, int $keepDays = 30): int
    {
        return Recommendation::byUser($userId)
            ->where('created_at', '<', now()->subDays($keepDays))
            ->delete();
    }
}
