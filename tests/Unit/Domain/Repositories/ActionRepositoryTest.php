<?php

namespace Tests\Unit\Domain\Repositories;

use App\Domain\EloquentModels\Action;
use App\Domain\Repositories\ActionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ActionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ActionRepository();
    }

    /**
     * Тест получения всех действий
     */
    public function test_get_all_actions(): void
    {
        Action::create([
            'type' => 'walk',
            'name' => 'Прогулка',
            'description' => 'Прогулка на свежем воздухе',
            'metadata' => ['icon' => '🚶'],
        ]);

        Action::create([
            'type' => 'sleep',
            'name' => 'Сон',
            'description' => 'Полноценный отдых',
            'metadata' => ['icon' => '😴'],
        ]);

        $actions = $this->repository->getAll();

        $this->assertCount(2, $actions);
        $this->assertEquals('walk', $actions[0]->type);
        $this->assertEquals('sleep', $actions[1]->type);
    }

    /**
     * Тест поиска действия по ID
     */
    public function test_find_action_by_id(): void
    {
        $action = Action::create([
            'type' => 'walk',
            'name' => 'Прогулка',
            'description' => 'Прогулка на свежем воздухе',
            'metadata' => ['icon' => '🚶'],
        ]);

        $found = $this->repository->findById($action->id);

        $this->assertNotNull($found);
        $this->assertEquals($action->id, $found->id);
        $this->assertEquals('walk', $found->type);
    }

    /**
     * Тест поиска несуществующего действия
     */
    public function test_find_non_existent_action_returns_null(): void
    {
        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    /**
     * Тест получения действий по типу
     */
    public function test_get_actions_by_type(): void
    {
        Action::create([
            'type' => 'walk',
            'name' => 'Прогулка',
            'description' => 'Прогулка на свежем воздухе',
            'metadata' => ['icon' => '🚶'],
        ]);

        Action::create([
            'type' => 'walk',
            'name' => 'Ходьба',
            'description' => 'Быстрая ходьба',
            'metadata' => ['icon' => '🚶'],
        ]);

        Action::create([
            'type' => 'sleep',
            'name' => 'Сон',
            'description' => 'Полноценный отдых',
            'metadata' => ['icon' => '😴'],
        ]);

        $walkActions = $this->repository->getByType('walk');
        $sleepActions = $this->repository->getByType('sleep');

        $this->assertCount(2, $walkActions);
        $this->assertCount(1, $sleepActions);

        foreach ($walkActions as $action) {
            $this->assertEquals('walk', $action->type);
        }
    }

    /**
     * Тест создания действия
     */
    public function test_create_action(): void
    {
        $data = [
            'type' => 'walk',
            'name' => 'Тестовая прогулка',
            'description' => 'Тестовое описание',
            'metadata' => ['test' => true],
        ];

        $action = $this->repository->create($data);

        $this->assertDatabaseHas('actions_service.actions', [
            'type' => 'walk',
            'name' => 'Тестовая прогулка',
        ]);
        $this->assertEquals($data['name'], $action->name);
        $this->assertEquals($data['type'], $action->type);
    }

    /**
     * Тест обновления действия
     */
    public function test_update_action(): void
    {
        $action = Action::create([
            'type' => 'walk',
            'name' => 'Старое имя',
            'description' => 'Старое описание',
            'metadata' => ['old' => true],
        ]);

        $updated = $this->repository->update($action->id, [
            'name' => 'Новое имя',
            'description' => 'Новое описание',
        ]);

        $this->assertTrue($updated);
        $this->assertDatabaseHas('actions_service.actions', [
            'id' => $action->id,
            'name' => 'Новое имя',
            'description' => 'Новое описание',
        ]);
    }

    /**
     * Тест обновления несуществующего действия
     */
    public function test_update_non_existent_action_returns_false(): void
    {
        $updated = $this->repository->update(99999, ['name' => 'Новое имя']);

        $this->assertFalse($updated);
    }

    /**
     * Тест удаления действия
     */
    public function test_delete_action(): void
    {
        $action = Action::create([
            'type' => 'walk',
            'name' => 'Для удаления',
            'description' => 'Будет удалено',
            'metadata' => [],
        ]);

        $deleted = $this->repository->delete($action->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('actions_service.actions', [
            'id' => $action->id,
        ]);
    }

    /**
     * Тест удаления несуществующего действия
     */
    public function test_delete_non_existent_action_returns_false(): void
    {
        $deleted = $this->repository->delete(99999);

        $this->assertFalse($deleted);
    }
}
