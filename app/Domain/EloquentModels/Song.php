<?php

namespace App\Domain\EloquentModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\SongFactory;

class Song extends Model
{
    use HasFactory;

    protected $table = 'actions_service.songs';

    protected $fillable = [
        'name',
        'artist',
        'album',
        'duration',
        'mood',
        'external_id',
        'metadata',
    ];

    protected $casts = [
        'mood' => 'array',
        'metadata' => 'array',
        'duration' => 'integer',
    ];

    /**
     * Скоуп для фильтрации по настроению
     */
    public function scopeByMood($query, string $mood)
    {
        return $query->whereJsonContains('mood', $mood);
    }

    /**
     * Скоуп для фильтрации по нескольким настроениям
     */
    public function scopeByMoods($query, array $moods)
    {
        foreach ($moods as $mood) {
            $query->whereJsonContains('mood', $mood);
        }
        return $query;
    }

    /**
     * Скоуп для фильтрации по исполнителю
     */
    public function scopeByArtist($query, string $artist)
    {
        return $query->where('artist', 'ILIKE', '%' . $artist . '%');
    }

    /**
     * Скоуп для фильтрации по внешнему источнику
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('metadata->source', $source);
    }

    /**
     * Создать новый экземпляр фабрики для модели
     */
    protected static function newFactory()
    {
        return SongFactory::new();
    }
}
