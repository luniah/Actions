<?php

namespace App\Domain\Repositories;

use App\Domain\EloquentModels\Song;
use Illuminate\Database\Eloquent\Collection;

class SongRepository
{
    /**
     * Получить все песни
     */
    public function getAll(): Collection
    {
        return Song::all();
    }

    /**
     * Найти песню по ID
     */
    public function findById(int $id): ?Song
    {
        return Song::find($id);
    }

    /**
     * Найти песню по внешнему ID (из API)
     */
    public function findByExternalId(string $externalId): ?Song
    {
        return Song::where('external_id', $externalId)->first();
    }

    /**
     * Получить песни по тегу (жанр/настроение)
     */
    public function getByTag(string $tag): Collection
    {
        return Song::byTag($tag)->get();
    }

    /**
     * Получить песни по нескольким тегам
     */
    public function getByTags(array $tags): Collection
    {
        return Song::byTags($tags)->get();
    }

    /**
     * Получить песни по исполнителю
     */
    public function getByArtist(string $artist): Collection
    {
        return Song::byArtist($artist)->get();
    }

    /**
     * Создать новую песню
     */
    public function create(array $data): Song
    {
        return Song::create($data);
    }

    /**
     * Обновить песню
     */
    public function update(int $id, array $data): bool
    {
        $song = Song::find($id);

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
        $song = Song::find($id);

        if (!$song) {
            return false;
        }

        return $song->delete();
    }
}
