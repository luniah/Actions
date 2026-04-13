<?php

namespace Tests\Unit\Domain\Repositories;

use App\Domain\EloquentModels\Action;
use App\Domain\Repositories\ActionRepository;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class ActionRepositoryTest extends TestCase
{
    private ActionRepository $repository;
    private $actionModelMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actionModelMock = Mockery::mock(Action::class);
        $this->repository = new ActionRepository($this->actionModelMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Тест получения всех действий
     */
    public function test_get_all_actions(): void
    {
        $actions = Action::factory()->count(5)->make();
        $collection = new Collection($actions);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('get')
            ->once()
            ->andReturn($collection);

        $this->actionModelMock
            ->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $result = $this->repository->getAll();

        $this->assertCount(5, $result);
    }

    /**
     * Тест поиска действия по ID
     */
    public function test_find_action_by_id(): void
    {
        $action = Action::factory()->make(['id' => 1]);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($action);

        $this->actionModelMock
            ->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $found = $this->repository->findById(1);

        $this->assertNotNull($found);
        $this->assertEquals(1, $found->id);
    }

    /**
     * Тест поиска несуществующего действия
     */
    public function test_find_non_existent_action_returns_null(): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')
            ->with(99999)
            ->once()
            ->andReturn(null);

        $this->actionModelMock
            ->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $found = $this->repository->findById(99999);

        $this->assertNull($found);
    }

    /**
     * Тест получения действий по типу
     */
    public function test_get_actions_by_type(): void
    {
        $actions = Action::factory()->walk()->count(3)->make();
        $collection = new Collection($actions);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')
            ->with('type', Action::TYPE_WALK)
            ->once()
            ->andReturnSelf();
        $queryMock->shouldReceive('get')
            ->once()
            ->andReturn($collection);

        $this->actionModelMock
            ->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $result = $this->repository->getByType(Action::TYPE_WALK);

        $this->assertCount(3, $result);
    }

    /**
     * Тест создания действия
     */
    public function test_create_action(): void
    {
        $data = [
            'type' => Action::TYPE_WALK,
            'name' => 'Тестовая прогулка',
            'description' => 'Тестовое описание',
        ];

        $action = Action::factory()->make($data);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('create')
            ->with($data)
            ->once()
            ->andReturn($action);

        $this->actionModelMock
            ->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $result = $this->repository->create($data);

        $this->assertEquals('Тестовая прогулка', $result->name);
    }

    /**
     * Тест обновления действия
     */
    public function test_update_action(): void
    {
        $actionMock = Mockery::mock(Action::class);
        $actionMock->shouldReceive('update')
            ->with(['name' => 'Новое имя'])
            ->once()
            ->andReturn(true);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($actionMock);

        $this->actionModelMock
            ->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $updated = $this->repository->update(1, ['name' => 'Новое имя']);
        $this->assertTrue($updated);
    }

    /**
     * Тест удаления действия
     */
    public function test_delete_action(): void
    {
        $actionMock = Mockery::mock(Action::class);
        $actionMock->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($actionMock);

        $this->actionModelMock
            ->shouldReceive('query')
            ->once()
            ->andReturn($queryMock);

        $deleted = $this->repository->delete(1);
        $this->assertTrue($deleted);
    }
}
