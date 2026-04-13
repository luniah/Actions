<?php

namespace Tests\Unit\Domain\Repositories;

use App\Domain\EloquentModels\Recommendation;
use App\Domain\Repositories\RecommendationRepository;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class RecommendationRepositoryTest extends TestCase
{
    private RecommendationRepository $repository;
    private $recommendationModelMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recommendationModelMock = Mockery::mock(Recommendation::class);
        $this->repository = new RecommendationRepository($this->recommendationModelMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Тест получения всех рекомендаций
     */
    public function test_get_all_recommendations(): void
    {
        $recommendations = Recommendation::factory()->count(5)->make();
        $collection = new Collection($recommendations);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('latest')->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getAll();
        $this->assertCount(5, $result);
    }

    /**
     * Тест поиска рекомендации по ID
     */
    public function test_find_recommendation_by_id(): void
    {
        $recommendation = Recommendation::factory()->make(['id' => 1]);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(1)->once()->andReturn($recommendation);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $found = $this->repository->findById(1);
        $this->assertNotNull($found);
        $this->assertEquals(1, $found->id);
    }

    /**
     * Тест поиска несуществующей рекомендации
     */
    public function test_find_non_existent_recommendation_returns_null(): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(99999)->once()->andReturn(null);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $found = $this->repository->findById(99999);
        $this->assertNull($found);
    }

    /**
     * Тест получения рекомендаций для пользователя
     */
    public function test_get_recommendations_by_user(): void
    {
        $recommendations = Recommendation::factory()->forUser(123)->count(3)->make();
        $collection = new Collection($recommendations);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('user_id', 123)->once()->andReturnSelf();
        $queryMock->shouldReceive('latest')->once()->andReturnSelf();
        $queryMock->shouldReceive('limit')->with(20)->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByUser(123);
        $this->assertCount(3, $result);
    }

    /**
     * Тест получения рекомендаций для пользователя с кастомным лимитом
     */
    public function test_get_recommendations_by_user_with_custom_limit(): void
    {
        $recommendations = Recommendation::factory()->forUser(123)->count(5)->make();
        $collection = new Collection($recommendations);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('user_id', 123)->once()->andReturnSelf();
        $queryMock->shouldReceive('latest')->once()->andReturnSelf();
        $queryMock->shouldReceive('limit')->with(10)->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByUser(123, 10);
        $this->assertCount(5, $result);
    }

    /**
     * Тест получения последних рекомендаций для пользователя
     */
    public function test_get_recent_recommendations_by_user(): void
    {
        $recommendations = Recommendation::factory()->forUser(123)->count(2)->make();
        $collection = new Collection($recommendations);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('user_id', 123)->once()->andReturnSelf();
        $queryMock->shouldReceive('where')->with('created_at', '>=', Mockery::type('Illuminate\Support\Carbon'))->once()->andReturnSelf();
        $queryMock->shouldReceive('latest')->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getRecentByUser(123, 7);
        $this->assertCount(2, $result);
    }

    /**
     * Тест получения последних рекомендаций с кастомным количеством дней
     */
    public function test_get_recent_recommendations_with_custom_days(): void
    {
        $recommendations = Recommendation::factory()->forUser(123)->count(1)->make();
        $collection = new Collection($recommendations);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('user_id', 123)->once()->andReturnSelf();
        $queryMock->shouldReceive('where')->with('created_at', '>=', Mockery::type('Illuminate\Support\Carbon'))->once()->andReturnSelf();
        $queryMock->shouldReceive('latest')->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getRecentByUser(123, 30);
        $this->assertCount(1, $result);
    }

    /**
     * Тест получения рекомендаций по типу действия
     */
    public function test_get_recommendations_by_action_type(): void
    {
        $recommendations = Recommendation::factory()->ofType('watch_movie')->count(3)->make();
        $collection = new Collection($recommendations);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('user_id', 123)->once()->andReturnSelf();
        $queryMock->shouldReceive('where')->with('action_type', 'watch_movie')->once()->andReturnSelf();
        $queryMock->shouldReceive('latest')->once()->andReturnSelf();
        $queryMock->shouldReceive('limit')->with(10)->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByActionType(123, 'watch_movie');
        $this->assertCount(3, $result);
    }

    /**
     * Тест получения рекомендаций по типу действия с кастомным лимитом
     */
    public function test_get_recommendations_by_action_type_with_custom_limit(): void
    {
        $recommendations = Recommendation::factory()->ofType('listen_music')->count(5)->make();
        $collection = new Collection($recommendations);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('user_id', 123)->once()->andReturnSelf();
        $queryMock->shouldReceive('where')->with('action_type', 'listen_music')->once()->andReturnSelf();
        $queryMock->shouldReceive('latest')->once()->andReturnSelf();
        $queryMock->shouldReceive('limit')->with(5)->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByActionType(123, 'listen_music', 5);
        $this->assertCount(5, $result);
    }

    /**
     * Тест создания рекомендации
     */
    public function test_create_recommendation(): void
    {
        $data = [
            'user_id' => 123,
            'action_type' => 'watch_movie',
            'action_id' => 3,
            'context' => ['season' => 'WINTER'],
            'suggestions' => [['type' => 'movie', 'id' => 1]],
        ];

        $recommendation = Recommendation::factory()->make($data);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('create')->with($data)->once()->andReturn($recommendation);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->create($data);
        $this->assertEquals(123, $result->user_id);
        $this->assertEquals('watch_movie', $result->action_type);
    }

    /**
     * Тест удаления рекомендации
     */
    public function test_delete_recommendation(): void
    {
        $recMock = Mockery::mock(Recommendation::class);
        $recMock->shouldReceive('delete')->once()->andReturn(true);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(1)->once()->andReturn($recMock);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $deleted = $this->repository->delete(1);
        $this->assertTrue($deleted);
    }

    /**
     * Тест удаления несуществующей рекомендации
     */
    public function test_delete_non_existent_recommendation_returns_false(): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(99999)->once()->andReturn(null);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $deleted = $this->repository->delete(99999);
        $this->assertFalse($deleted);
    }

    /**
     * Тест удаления старых рекомендаций пользователя
     */
    public function test_delete_old_recommendations_for_user(): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('user_id', 123)->once()->andReturnSelf();
        $queryMock->shouldReceive('where')->with('created_at', '<', Mockery::type('Illuminate\Support\Carbon'))->once()->andReturnSelf();
        $queryMock->shouldReceive('delete')->once()->andReturn(5);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $deletedCount = $this->repository->deleteOldForUser(123, 30);
        $this->assertEquals(5, $deletedCount);
    }

    /**
     * Тест удаления старых рекомендаций с кастомным количеством дней
     */
    public function test_delete_old_recommendations_with_custom_days(): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('user_id', 456)->once()->andReturnSelf();
        $queryMock->shouldReceive('where')->with('created_at', '<', Mockery::type('Illuminate\Support\Carbon'))->once()->andReturnSelf();
        $queryMock->shouldReceive('delete')->once()->andReturn(3);
        $this->recommendationModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $deletedCount = $this->repository->deleteOldForUser(456, 60);
        $this->assertEquals(3, $deletedCount);
    }
}
