<?php

namespace Tests\Unit\Application\Services;

use App\Application\Models\WorldContextModel;
use App\Application\Services\RecommendationService;
use App\Application\Services\WorldContextAnalyzer;
use App\Application\Services\WorldServiceClient;
use App\Domain\Repositories\ActionRepository;
use App\Domain\Repositories\RecommendationRepository;
use App\Domain\Repositories\SongRepository;
use App\Domain\Repositories\MovieRepository;
use App\Domain\EloquentModels\Action;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    use RefreshDatabase;

    private RecommendationService $service;
    private ActionRepository $actionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём JSON-файлы для тестов
        $musicPath = storage_path('app/data/music.json');
        $moviesPath = storage_path('app/data/movies.json');

        if (!is_dir(dirname($musicPath))) {
            mkdir(dirname($musicPath), 0755, true);
        }

        file_put_contents($musicPath, json_encode(['tracks' => []]));
        file_put_contents($moviesPath, json_encode(['movies' => []]));

        $this->actionRepository = new ActionRepository();
        $contextAnalyzer = new WorldContextAnalyzer();
        $recommendationRepository = new RecommendationRepository();
        $songRepository = new SongRepository();
        $movieRepository = new MovieRepository();

        $this->service = new RecommendationService(
            $recommendationRepository,
            $this->actionRepository,
            $contextAnalyzer
        );

        // Создаём действия в БД
        $this->seedActions();
    }

    /**
     * Создать тестовые действия
     */
    private function seedActions(): void
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

        Action::create([
            'type' => 'watch_movie',
            'name' => 'Просмотр фильма',
            'description' => 'Просмотр фильма',
            'metadata' => ['icon' => '🎬'],
        ]);

        Action::create([
            'type' => 'listen_music',
            'name' => 'Прослушивание музыки',
            'description' => 'Прослушивание музыки',
            'metadata' => ['icon' => '🎵'],
        ]);

        Action::create([
            'type' => 'visit_place',
            'name' => 'Посещение места',
            'description' => 'Посещение места',
            'metadata' => ['icon' => '📍'],
        ]);
    }

    /**
     * Тест выбора типа действия для утра с хорошей погодой
     */
    public function test_recommend_morning_good_weather(): void
    {
        $context = $this->createContext('MORNING', 'SUMMER', 'CLEAR', 22);

        $recommendation = $this->service->recommend($context);

        $this->assertEquals('123', $recommendation->userId);
        $this->assertContains($recommendation->actionType, ['walk', 'visit_place']);
    }

    /**
     * Тест выбора типа действия для ночи
     */
    public function test_recommend_night_returns_sleep(): void
    {
        $context = $this->createContext('NIGHT', 'WINTER', 'CLEAR', -5);

        $recommendation = $this->service->recommend($context);

        $this->assertEquals('sleep', $recommendation->actionType);
    }

    /**
     * Тест выбора типа действия для дождливой погоды
     */
    public function test_recommend_rainy_weather_returns_indoor_activity(): void
    {
        $context = $this->createContext('AFTERNOON', 'AUTUMN', 'RAINY', 10);

        $recommendation = $this->service->recommend($context);

        $this->assertEquals('watch_movie', $recommendation->actionType);
    }

    /**
     * Тест получения истории рекомендаций
     */
    public function test_get_user_history(): void
    {
        $context = $this->createContext('EVENING', 'SPRING', 'CLEAR', 20);

        $this->service->recommend($context);
        $this->service->recommend($context);
        $this->service->recommend($context);

        $history = $this->service->getUserHistory(123, 10);

        $this->assertCount(3, $history);
        $this->assertEquals(123, $history[0]['user_id']);
    }

    /**
     * Создать контекст для тестов
     */
    private function createContext(string $dayTime, string $season, string $condition, float $temperature): WorldContextModel
    {
        return new WorldContextModel(
            userId: '123',
            latitude: 55.7558,
            longitude: 37.6176,
            dayTime: $dayTime,
            season: $season,
            weather: [
                'condition' => $condition,
                'temperature' => $temperature,
                'feelsLike' => $temperature - 2,
                'humidity' => 70,
                'windSpeed' => 5,
            ],
            entertainment: [
                'nearby_places' => [],
                'places_by_category' => [],
            ],
            sunrise: '06:00',
            sunset: '20:00',
            updatedAt: now()->toIso8601String(),
        );
    }
}
