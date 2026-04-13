<?php

namespace App\Domain\EloquentModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\ActionFactory;

class Action extends Model
{
    use HasFactory;

    protected $table = 'actions_service.actions';

    protected $fillable = [
        'type', 'name', 'description', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public const TYPE_WALK = 'walk';
    public const TYPE_SLEEP = 'sleep';
    public const TYPE_WATCH_MOVIE = 'watch_movie';
    public const TYPE_LISTEN_MUSIC = 'listen_music';
    public const TYPE_VISIT_PLACE = 'visit_place';

    /**
     * Скоуп для фильтрации действий по типу
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Создать новый экземпляр фабрики для модели
     */
    protected static function newFactory()
    {
        return ActionFactory::new();
    }
}
