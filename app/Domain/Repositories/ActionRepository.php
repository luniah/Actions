<?php

namespace App\Domain\Repositories;

use App\Domain\EloquentModels\Action;
use Illuminate\Database\Eloquent\Collection;

class ActionRepository
{
    /**
     * Получить все действия
     */
    public function getAll(): Collection
    {
        return Action::all();
    }

    /**
     * Найти действие по ID
     */
    public function findById(int $id): ?Action
    {
        return Action::find($id);
    }

    /**
     * Получить действия по типу
     */
    public function getByType(string $type): Collection
    {
        return Action::byType($type)->get();
    }

    /**
     * Создать новое действие
     */
    public function create(array $data): Action
    {
        return Action::create($data);
    }

    /**
     * Обновить действие
     */
    public function update(int $id, array $data): bool
    {
        $action = Action::find($id);

        if (!$action) {
            return false;
        }

        return $action->update($data);
    }

    /**
     * Удалить действие
     */
    public function delete(int $id): bool
    {
        $action = Action::find($id);

        if (!$action) {
            return false;
        }

        return $action->delete();
    }
}
