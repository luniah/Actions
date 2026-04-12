<?php

namespace Tests\Unit\Domain\Repositories;

use App\Domain\EloquentModels\Song;
use App\Domain\Repositories\SongRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private SongRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new SongRepository();
    }

    /**
     * Тест получения всех песен
     */
    public function test_get_all_songs(): void
    {
        Song::create([
            'name' => 'Test Song 1',
            'artist' => 'Test Artist 1',
            'album' => 'Test Album 1',
            'duration' => 180,
            'mood' => ['happy', 'energetic'],
            'external_id' => 'test_1',
        ]);

        Song::create([
            'name' => 'Test Song 2',
            'artist' => 'Test Artist 2',
            'album' => 'Test Album 2',
            'duration' => 240,
            'mood' => ['calm', 'peaceful'],
            'external_id' => 'test_2',
        ]);

        $songs = $this->repository->getAll();

        $this->assertCount(2, $songs);
    }

    /**
     * Тест поиска песни по ID
     */
    public function test_find_song_by_id(): void
    {
        $song = Song::create([
            'name' => 'Test Song',
            'artist' => 'Test Artist',
            'album' => 'Test Album',
            'duration' => 180,
            'mood' => ['happy'],
            'external_id' => 'test_123',
        ]);

        $found = $this->repository->findById($song->id);

        $this->assertNotNull($found);
        $this->assertEquals($song->id, $found->id);
        $this->assertEquals('Test Song', $found->name);
    }

    /**
     * Тест поиска песни по внешнему ID
     */
    public function test_find_song_by_external_id(): void
    {
        Song::create([
            'name' => 'Test Song',
            'artist' => 'Test Artist',
            'album' => 'Test Album',
            'duration' => 180,
            'mood' => ['happy'],
            'external_id' => 'spotify_123',
        ]);

        $found = $this->repository->findByExternalId('spotify_123');
        $notFound = $this->repository->findByExternalId('non_existent');

        $this->assertNotNull($found);
        $this->assertEquals('Test Song', $found->name);
        $this->assertNull($notFound);
    }

    /**
     * Тест получения песен по настроению
     */
    public function test_get_songs_by_mood(): void
    {
        Song::create([
            'name' => 'Energetic Song',
            'artist' => 'Artist 1',
            'mood' => ['energetic', 'happy'],
            'external_id' => 'test_1',
        ]);

        Song::create([
            'name' => 'Calm Song',
            'artist' => 'Artist 2',
            'mood' => ['calm', 'peaceful'],
            'external_id' => 'test_2',
        ]);

        Song::create([
            'name' => 'Mixed Song',
            'artist' => 'Artist 3',
            'mood' => ['happy', 'calm'],
            'external_id' => 'test_3',
        ]);

        $energetic = $this->repository->getByMood('energetic');
        $calm = $this->repository->getByMood('calm');
        $happy = $this->repository->getByMood('happy');

        $this->assertCount(1, $energetic);
        $this->assertCount(2, $calm);
        $this->assertCount(2, $happy);
    }

    /**
     * Тест получения песен по нескольким настроениям
     */
    public function test_get_songs_by_multiple_moods(): void
    {
        Song::create([
            'name' => 'Happy Energetic Song',
            'artist' => 'Artist 1',
            'mood' => ['happy', 'energetic'],
            'external_id' => 'test_1',
        ]);

        Song::create([
            'name' => 'Happy Calm Song',
            'artist' => 'Artist 2',
            'mood' => ['happy', 'calm'],
            'external_id' => 'test_2',
        ]);

        Song::create([
            'name' => 'Sad Calm Song',
            'artist' => 'Artist 3',
            'mood' => ['sad', 'calm'],
            'external_id' => 'test_3',
        ]);

        $happyAndEnergetic = $this->repository->getByMoods(['happy', 'energetic']);

        $this->assertCount(1, $happyAndEnergetic);
        $this->assertEquals('Happy Energetic Song', $happyAndEnergetic[0]->name);
    }

    /**
     * Тест получения песен по исполнителю
     */
    public function test_get_songs_by_artist(): void
    {
        Song::create([
            'name' => 'Song 1',
            'artist' => 'The Beatles',
            'external_id' => 'test_1',
        ]);

        Song::create([
            'name' => 'Song 2',
            'artist' => 'Queen',
            'external_id' => 'test_2',
        ]);

        Song::create([
            'name' => 'Song 3',
            'artist' => 'The Beatles',
            'external_id' => 'test_3',
        ]);

        $beatlesSongs = $this->repository->getByArtist('Beatles');

        $this->assertCount(2, $beatlesSongs);
    }

    /**
     * Тест создания песни
     */
    public function test_create_song(): void
    {
        $data = [
            'name' => 'New Song',
            'artist' => 'New Artist',
            'album' => 'New Album',
            'duration' => 200,
            'mood' => ['happy', 'dance'],
            'external_id' => 'new_123',
            'metadata' => ['genre' => 'pop'],
        ];

        $song = $this->repository->create($data);

        $this->assertDatabaseHas('actions_service.songs', [
            'name' => 'New Song',
            'artist' => 'New Artist',
            'external_id' => 'new_123',
        ]);
        $this->assertEquals($data['name'], $song->name);
    }

    /**
     * Тест обновления песни
     */
    public function test_update_song(): void
    {
        $song = Song::create([
            'name' => 'Old Name',
            'artist' => 'Old Artist',
            'external_id' => 'test_123',
        ]);

        $updated = $this->repository->update($song->id, [
            'name' => 'New Name',
            'artist' => 'New Artist',
        ]);

        $this->assertTrue($updated);
        $this->assertDatabaseHas('actions_service.songs', [
            'id' => $song->id,
            'name' => 'New Name',
            'artist' => 'New Artist',
        ]);
    }

    /**
     * Тест удаления песни
     */
    public function test_delete_song(): void
    {
        $song = Song::create([
            'name' => 'To Delete',
            'artist' => 'Artist',
            'external_id' => 'delete_123',
        ]);

        $deleted = $this->repository->delete($song->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('actions_service.songs', [
            'id' => $song->id,
        ]);
    }

    /**
     * Тест получения всех уникальных настроений
     */
    public function test_get_all_moods(): void
    {
        Song::create([
            'name' => 'Song 1',
            'artist' => 'Artist 1',
            'mood' => ['happy', 'energetic'],
            'external_id' => 'test_1',
        ]);

        Song::create([
            'name' => 'Song 2',
            'artist' => 'Artist 2',
            'mood' => ['calm', 'happy'],
            'external_id' => 'test_2',
        ]);

        Song::create([
            'name' => 'Song 3',
            'artist' => 'Artist 3',
            'mood' => ['sad', 'melancholic'],
            'external_id' => 'test_3',
        ]);

        $moods = $this->repository->getAllMoods();

        $this->assertContains('happy', $moods);
        $this->assertContains('energetic', $moods);
        $this->assertContains('calm', $moods);
        $this->assertContains('sad', $moods);
        $this->assertContains('melancholic', $moods);
    }

    /**
     * Тест пагинации
     */
    public function test_paginate_songs(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            Song::create([
                'name' => "Song {$i}",
                'artist' => "Artist {$i}",
                'external_id' => "test_{$i}",
            ]);
        }

        $paginated = $this->repository->paginate(10);

        $this->assertEquals(25, $paginated->total());
        $this->assertCount(10, $paginated->items());
        $this->assertEquals(3, $paginated->lastPage());
    }

    /**
     * Тест подсчёта количества песен
     */
    public function test_count_songs(): void
    {
        Song::create([
            'name' => 'Song 1',
            'artist' => 'Artist 1',
            'external_id' => 'test_1',
        ]);

        Song::create([
            'name' => 'Song 2',
            'artist' => 'Artist 2',
            'external_id' => 'test_2',
        ]);

        Song::create([
            'name' => 'Song 3',
            'artist' => 'Artist 3',
            'external_id' => 'test_3',
        ]);

        $count = $this->repository->count();

        $this->assertEquals(3, $count);
    }
}
