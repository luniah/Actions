<?php

namespace App\Domain\Repositories;

use App\Domain\EloquentModels\Movie;
use Illuminate\Database\Eloquent\Collection;

class MovieRepository
{
    public function __construct(
        private readonly Movie $model = new Movie()
    ) {}

    /**
     * Получить все фильмы
     */
    public function getAll(): Collection
    {
        return $this->model->query()->get();
    }

    /**
     * Найти фильм по ID
     */
    public function findById(int $id): ?Movie
    {
        return $this->model->query()->find($id);
    }

    /**
     * Найти фильм по внешнему ID (из API)
     */
    public function findByExternalId(string $externalId): ?Movie
    {
        return $this->model->query()->where('external_id', $externalId)->first();
    }

    /**
     * Получить фильмы по жанру
     */
    public function getByGenre(string $genre): Collection
    {
        return $this->model->query()->whereJsonContains('genres', $genre)->get();
    }

    /**
     * Получить фильмы по нескольким жанрам
     */
    public function getByGenres(array $genres): Collection
    {
        $query = $this->model->query();

        foreach ($genres as $genre) {
            $query->whereJsonContains('genres', $genre);
        }

        return $query->get();
    }

    /**
     * Получить фильмы по году выпуска
     */
    public function getByReleaseYear(int $year): Collection
    {
        return $this->model->query()->where('release_year', $year)->get();
    }

    /**
     * Получить фильмы по режиссёру
     */
    public function getByDirector(string $director): Collection
    {
        return $this->model->query()->where('director', $director)->get();
    }

    /**
     * Создать новый фильм
     */
    public function create(array $data): Movie
    {
        return $this->model->query()->create($data);
    }

    /**
     * Обновить фильм
     */
    public function update(int $id, array $data): bool
    {
        $movie = $this->model->query()->find($id);

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
        $movie = $this->model->query()->find($id);

        if (!$movie) {
            return false;
        }

        return $movie->delete();
    }
}
