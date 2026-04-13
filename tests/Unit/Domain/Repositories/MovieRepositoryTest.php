<?php

namespace Tests\Unit\Domain\Repositories;

use App\Domain\EloquentModels\Movie;
use App\Domain\Repositories\MovieRepository;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class MovieRepositoryTest extends TestCase
{
    private MovieRepository $repository;
    private $movieModelMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->movieModelMock = Mockery::mock(Movie::class);
        $this->repository = new MovieRepository($this->movieModelMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Тест получения всех фильмов
     */
    public function test_get_all_movies(): void
    {
        $movies = Movie::factory()->count(5)->make();
        $collection = new Collection($movies);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getAll();
        $this->assertCount(5, $result);
    }

    /**
     * Тест поиска фильма по ID
     */
    public function test_find_movie_by_id(): void
    {
        $movie = Movie::factory()->make(['id' => 1]);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(1)->once()->andReturn($movie);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $found = $this->repository->findById(1);
        $this->assertNotNull($found);
        $this->assertEquals(1, $found->id);
    }

    /**
     * Тест поиска несуществующего фильма
     */
    public function test_find_non_existent_movie_returns_null(): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(99999)->once()->andReturn(null);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $found = $this->repository->findById(99999);
        $this->assertNull($found);
    }

    /**
     * Тест поиска фильма по внешнему ID
     */
    public function test_find_movie_by_external_id(): void
    {
        $movie = Movie::factory()->make(['id' => 1, 'external_id' => 'tmdb_123']);
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('external_id', 'tmdb_123')->once()->andReturnSelf();
        $queryMock->shouldReceive('first')->once()->andReturn($movie);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $found = $this->repository->findByExternalId('tmdb_123');
        $this->assertNotNull($found);
        $this->assertEquals('tmdb_123', $found->external_id);
    }

    /**
     * Тест получения фильмов по жанру
     */
    public function test_get_movies_by_genre(): void
    {
        $movies = Movie::factory()->drama()->count(3)->make();
        $collection = new Collection($movies);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('whereJsonContains')->with('genres', 'драма')->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByGenre('драма');
        $this->assertCount(3, $result);
    }

    /**
     * Тест получения фильмов по нескольким жанрам
     */
    public function test_get_movies_by_genres(): void
    {
        $movies = Movie::factory()->count(2)->make();
        $collection = new Collection($movies);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('whereJsonContains')->with('genres', 'драма')->once()->andReturnSelf();
        $queryMock->shouldReceive('whereJsonContains')->with('genres', 'криминал')->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByGenres(['драма', 'криминал']);
        $this->assertCount(2, $result);
    }

    /**
     * Тест получения фильмов по году выпуска
     */
    public function test_get_movies_by_release_year(): void
    {
        $movies = Movie::factory()->count(2)->make();
        $collection = new Collection($movies);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('release_year', 1994)->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByReleaseYear(1994);
        $this->assertCount(2, $result);
    }

    /**
     * Тест получения фильмов по режиссёру
     */
    public function test_get_movies_by_director(): void
    {
        $movies = Movie::factory()->count(2)->make();
        $collection = new Collection($movies);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('director', 'Кристофер Нолан')->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByDirector('Кристофер Нолан');
        $this->assertCount(2, $result);
    }

    /**
     * Тест создания фильма
     */
    public function test_create_movie(): void
    {
        $data = [
            'title' => 'Тестовый фильм',
            'description' => 'Тестовое описание',
            'genres' => ['драма', 'криминал'],
            'release_year' => 2020,
            'duration' => 120,
            'director' => 'Тестовый режиссёр',
            'external_id' => 'tmdb_test',
        ];

        $movie = Movie::factory()->make($data);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('create')->with($data)->once()->andReturn($movie);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->create($data);
        $this->assertEquals('Тестовый фильм', $result->title);
    }

    /**
     * Тест обновления фильма
     */
    public function test_update_movie(): void
    {
        $movieMock = Mockery::mock(Movie::class);
        $movieMock->shouldReceive('update')->with(['title' => 'Новое название'])->once()->andReturn(true);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(1)->once()->andReturn($movieMock);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $updated = $this->repository->update(1, ['title' => 'Новое название']);
        $this->assertTrue($updated);
    }

    /**
     * Тест обновления несуществующего фильма
     */
    public function test_update_non_existent_movie_returns_false(): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(99999)->once()->andReturn(null);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $updated = $this->repository->update(99999, ['title' => 'Новое название']);
        $this->assertFalse($updated);
    }

    /**
     * Тест удаления фильма
     */
    public function test_delete_movie(): void
    {
        $movieMock = Mockery::mock(Movie::class);
        $movieMock->shouldReceive('delete')->once()->andReturn(true);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(1)->once()->andReturn($movieMock);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $deleted = $this->repository->delete(1);
        $this->assertTrue($deleted);
    }

    /**
     * Тест удаления несуществующего фильма
     */
    public function test_delete_non_existent_movie_returns_false(): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(99999)->once()->andReturn(null);
        $this->movieModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $deleted = $this->repository->delete(99999);
        $this->assertFalse($deleted);
    }
}
