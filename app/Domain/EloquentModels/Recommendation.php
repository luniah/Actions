<?php

namespace App\Domain\EloquentModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\RecommendationFactory;

class Recommendation extends Model
{
    use HasFactory;

    protected $table = 'actions_service.recommendations';

    protected $fillable = [
        'user_id',
        'action_type',
        'action_id',
        'context',
        'suggestions',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'suggestions' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Скоуп для фильтрации по пользователю
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Скоуп для фильтрации по типу действия
     */
    public function scopeByActionType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Скоуп для получения рекомендаций за последние N дней
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Создать новый экземпляр фабрики для модели
     */
    protected static function newFactory()
    {
        return RecommendationFactory::new();
    }
}
