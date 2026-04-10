<?php

namespace App\Domain\Repositories;

use App\Domain\EloquentModels\Movie;
use Illuminate\Database\Eloquent\Collection;

class MovieRepository
{
    /**
     * Получить все фильмы
     */
    public function getAll(): Collection
    {
        return Movie::all();
    }

    /**
     * Найти фильм по ID
     */
    public function findById(int $id): ?Movie
    {
        return Movie::find($id);
    }

    /**
     * Найти фильм по внешнему ID (из API)
     */
    public function findByExternalId(string $externalId): ?Movie
    {
        return Movie::where('external_id', $externalId)->first();
    }

    /**
     * Получить фильмы по жанру
     */
    public function getByGenre(string $genre): Collection
    {
        return Movie::byGenre($genre)->get();
    }

    /**
     * Получить фильмы по нескольким жанрам
     */
    public function getByGenres(array $genres): Collection
    {
        return Movie::byGenres($genres)->get();
    }

    /**
     * Получить фильмы по году выпуска
     */
    public function getByReleaseYear(int $year): Collection
    {
        return Movie::byReleaseYear($year)->get();
    }

    /**
     * Получить фильмы по режиссёру
     */
    public function getByDirector(string $director): Collection
    {
        return Movie::byDirector($director)->get();
    }

    /**
     * Создать новый фильм
     */
    public function create(array $data): Movie
    {
        return Movie::create($data);
    }

    /**
     * Обновить фильм
     */
    public function update(int $id, array $data): bool
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return false;
        }

        return $movie->update($data);
    }

    /**
     * Удалить фильм
     */
    public function delete(int $id): bool
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return false;
        }

        return $movie->delete();
    }
}
