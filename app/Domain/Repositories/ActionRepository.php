<?php

namespace App\Domain\Repositories;

use App\Domain\EloquentModels\Action;
use Illuminate\Database\Eloquent\Collection;

class ActionRepository
{
    public function __construct(
        private readonly Action $model = new Action()
    ) {}

    /**
     * Получить все действия
     */
    public function getAll(): Collection
    {
        return $this->model->query()->get();
    }

    /**
     * Найти действие по ID
     */
    public function findById(int $id): ?Action
    {
        return $this->model->query()->find($id);
    }

    /**
     * Получить действия по типу
     */
    public function getByType(string $type): Collection
    {
        return $this->model->query()->where('type', $type)->get();
    }

    /**
     * Создать новое действие
     */
    public function create(array $data): Action
    {
        return $this->model->query()->create($data);
    }

    /**
     * Обновить действие
     */
    public function update(int $id, array $data): bool
    {
        $action = $this->model->query()->find($id);

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
        $action = $this->model->query()->find($id);

        if (!$action) {
            return false;
        }

        return $action->delete();
    }
}
