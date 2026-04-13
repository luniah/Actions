<?php

namespace Tests\Unit\Domain\Repositories;

use App\Domain\EloquentModels\Song;
use App\Domain\Repositories\SongRepository;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class SongRepositoryTest extends TestCase
{
    private SongRepository $repository;
    private $songModelMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->songModelMock = Mockery::mock(Song::class);
        $this->repository = new SongRepository($this->songModelMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Тест получения всех песен
     */
    public function test_get_all_songs(): void
    {
        $songs = Song::factory()->count(3)->make();
        $collection = new Collection($songs);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getAll();
        $this->assertCount(3, $result);
    }

    /**
     * Тест поиска песни по ID
     */
    public function test_find_song_by_id(): void
    {
        $song = Song::factory()->make(['id' => 5]);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(5)->once()->andReturn($song);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $found = $this->repository->findById(5);
        $this->assertNotNull($found);
        $this->assertEquals(5, $found->id);
    }

    /**
     * Тест поиска песни по внешнему ID
     */
    public function test_find_song_by_external_id(): void
    {
        $song = Song::factory()->make(['id' => 1, 'external_id' => 'spotify_123']);
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('external_id', 'spotify_123')->once()->andReturnSelf();
        $queryMock->shouldReceive('first')->once()->andReturn($song);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $found = $this->repository->findByExternalId('spotify_123');
        $this->assertNotNull($found);
        $this->assertEquals('spotify_123', $found->external_id);
    }

    /**
     * Тест получения песен по настроению
     */
    public function test_get_songs_by_mood(): void
    {
        $songs = Song::factory()->energetic()->count(2)->make();
        $collection = new Collection($songs);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('whereJsonContains')->with('mood', 'energetic')->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByMood('energetic');
        $this->assertCount(2, $result);
    }

    /**
     * Тест получения песен по нескольким настроениям
     */
    public function test_get_songs_by_multiple_moods(): void
    {
        $songs = Song::factory()->count(1)->make();
        $collection = new Collection($songs);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('whereJsonContains')->with('mood', 'happy')->once()->andReturnSelf();
        $queryMock->shouldReceive('whereJsonContains')->with('mood', 'energetic')->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByMoods(['happy', 'energetic']);
        $this->assertCount(1, $result);
    }

    /**
     * Тест получения песен по исполнителю
     */
    public function test_get_songs_by_artist(): void
    {
        $songs = Song::factory()->count(2)->make();
        $collection = new Collection($songs);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')->with('artist', 'ILIKE', '%Beatles%')->once()->andReturnSelf();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->getByArtist('Beatles');
        $this->assertCount(2, $result);
    }

    /**
     * Тест создания песни
     */
    public function test_create_song(): void
    {
        $data = ['name' => 'New Song', 'artist' => 'Artist'];
        $song = Song::factory()->make($data);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('create')->with($data)->once()->andReturn($song);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->create($data);
        $this->assertEquals('New Song', $result->name);
    }

    /**
     * Тест обновления песни
     */
    public function test_update_song(): void
    {
        $songMock = Mockery::mock(Song::class);
        $songMock->shouldReceive('update')->with(['name' => 'Updated'])->once()->andReturn(true);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(1)->once()->andReturn($songMock);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $updated = $this->repository->update(1, ['name' => 'Updated']);
        $this->assertTrue($updated);
    }

    /**
     * Тест удаления песни
     */
    public function test_delete_song(): void
    {
        $songMock = Mockery::mock(Song::class);
        $songMock->shouldReceive('delete')->once()->andReturn(true);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('find')->with(1)->once()->andReturn($songMock);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $deleted = $this->repository->delete(1);
        $this->assertTrue($deleted);
    }

    /**
     * Тест получения всех уникальных настроений
     */
    public function test_get_all_moods(): void
    {
        $song1 = Song::factory()->make(['mood' => ['happy', 'energetic']]);
        $song2 = Song::factory()->make(['mood' => ['calm', 'sad']]);
        $collection = new Collection([$song1, $song2]);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('get')->once()->andReturn($collection);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $moods = $this->repository->getAllMoods();
        $this->assertEquals(['happy', 'energetic', 'calm', 'sad'], $moods);
    }

    /**
     * Тест пагинации песен
     */
    public function test_paginate_songs(): void
    {
        $paginatorMock = Mockery::mock(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('paginate')->with(10)->once()->andReturn($paginatorMock);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $result = $this->repository->paginate(10);
        $this->assertSame($paginatorMock, $result);
    }

    /**
     * Тест подсчёта количества песен
     */
    public function test_count_songs(): void
    {
        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('count')->once()->andReturn(5);
        $this->songModelMock->shouldReceive('query')->once()->andReturn($queryMock);

        $count = $this->repository->count();
        $this->assertEquals(5, $count);
    }
}
