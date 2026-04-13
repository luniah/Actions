<?php

namespace App\Domain\EloquentModels;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\MovieFactory;

class Movie extends Model
{
    use HasFactory;

    protected $table = 'actions_service.movies';

    protected $fillable = [
        'title',
        'description',
        'genres',
        'release_year',
        'duration',
        'director',
        'poster_url',
        'external_id',
        'metadata',
    ];

    protected $casts = [
        'genres' => 'array',
        'metadata' => 'array',
        'release_year' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * Скоуп для фильтрации по жанру
     */
    public function scopeByGenre($query, string $genre)
    {
        return $query->whereJsonContains('genres', $genre);
    }

    /**
     * Скоуп для фильтрации по нескольким жанрам
     */
    public function scopeByGenres($query, array $genres)
    {
        foreach ($genres as $genre) {
            $query->whereJsonContains('genres', $genre);
        }
        return $query;
    }

    /**
     * Скоуп для фильтрации по году выпуска
     */
    public function scopeByReleaseYear($query, int $year)
    {
        return $query->where('release_year', $year);
    }

    /**
     * Скоуп для фильтрации по режиссёру
     */
    public function scopeByDirector($query, string $director)
    {
        return $query->where('director', $director);
    }

    /**
     * Создать новый экземпляр фабрики для модели
     */
    protected static function newFactory()
    {
        return MovieFactory::new();
    }
}
