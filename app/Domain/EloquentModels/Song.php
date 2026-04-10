<?php

namespace App\Domain\EloquentModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $table = 'actions_service.songs';

    protected $fillable = [
        'name',
        'artist',
        'album',
        'duration',
        'tags',
        'external_id',
        'metadata',
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'duration' => 'integer',
    ];

    /**
     * Скоуп для фильтрации по тегам (жанр/настроение)
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Скоуп для фильтрации по нескольким тегам
     */
    public function scopeByTags($query, array $tags)
    {
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags', $tag);
        }
        return $query;
    }

    /**
     * Скоуп для фильтрации по исполнителю
     */
    public function scopeByArtist($query, string $artist)
    {
        return $query->where('artist', $artist);
    }
}
