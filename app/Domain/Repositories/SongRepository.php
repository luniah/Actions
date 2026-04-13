<?php

namespace App\Domain\Repositories;

use App\Domain\EloquentModels\Song;
use Illuminate\Database\Eloquent\Collection;

class SongRepository
{
    public function __construct(
        private readonly Song $model = new Song()
    ) {}

    /**
     * Получить все песни
     */
    public function getAll(): Collection
    {
        return $this->model->query()->get();
    }

    /**
     * Найти песню по ID
     */
    public function findById(int $id): ?Song
    {
        return $this->model->query()->find($id);
    }

    /**
     * Найти песню по внешнему ID
     */
    public function findByExternalId(string $externalId): ?Song
    {
        return $this->model->query()->where('external_id', $externalId)->first();
    }

    /**
     * Получить песни по настроению
     */
    public function getByMood(string $mood): Collection
    {
        return $this->model->query()->whereJsonContains('mood', $mood)->get();
    }

    /**
     * Получить песни по нескольким настроениям
     */
    public function getByMoods(array $moods): Collection
    {
        $query = $this->model->query();

        foreach ($moods as $mood) {
            $query->whereJsonContains('mood', $mood);
        }

        return $query->get();
    }

    /**
     * Получить песни по исполнителю
     */
    public function getByArtist(string $artist): Collection
    {
        return $this->model->query()->where('artist', 'ILIKE', '%' . $artist . '%')->get();
    }

    /**
     * Получить песни по жанру (из mood)
     */
    public function getByGenre(string $genre): Collection
    {
        return $this->model->query()->whereJsonContains('mood', $genre)->get();
    }

    /**
     * Создать новую песню
     */
    public function create(array $data): Song
    {
        return $this->model->query()->create($data);
    }

    /**
     * Обновить песню
     */
    public function update(int $id, array $data): bool
    {
        $song = $this->model->query()->find($id);

        if (!$song) {
            return false;
        }

        return $song->update($data);
    }

    /**
     * Удалить песню
     */
    public function delete(int $id): bool
    {
        $song = $this->model->query()->find($id);

        if (!$song) {
            return false;
        }

        return $song->delete();
    }

    /**
     * Получить песни с пагинацией
     */
    public function paginate(int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->query()->paginate($perPage);
    }

    /**
     * Получить количество песен
     */
    public function count(): int
    {
        return $this->model->query()->count();
    }

    /**
     * Получить все уникальные настроения
     */
    public function getAllMoods(): array
    {
        $songs = $this->model->query()->get();
        $moods = [];

        foreach ($songs as $song) {
            if (!empty($song->mood)) {
                $moods = array_merge($moods, $song->mood);
            }
        }

        return array_unique($moods);
    }
}
