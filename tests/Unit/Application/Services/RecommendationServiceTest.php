<?php

namespace Tests\Unit\Application\Services;

use App\Application\Models\WorldContextModel;
use App\Application\Services\RecommendationService;
use App\Application\Services\WorldContextAnalyzer;
use App\Domain\Repositories\ActionRepository;
use App\Domain\Repositories\RecommendationRepository;
use App\Domain\EloquentModels\Action;
use App\Domain\EloquentModels\Recommendation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    private RecommendationService $service;
    private $recommendationRepositoryMock;
    private $actionRepositoryMock;
    private $contextAnalyzerMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Используем фейковую файловую систему вместо реальной
        Storage::fake('local');

        // Создаём пустые JSON-файлы в фейковом хранилище
        Storage::put('data/music.json', json_encode(['tracks' => []]));
        Storage::put('data/movies.json', json_encode(['movies' => []]));

        $this->recommendationRepositoryMock = Mockery::mock(RecommendationRepository::class);
        $this->actionRepositoryMock = Mockery::mock(ActionRepository::class);
        $this->contextAnalyzerMock = Mockery::mock(WorldContextAnalyzer::class);

        $this->service = new RecommendationService(
            $this->recommendationRepositoryMock,
            $this->actionRepositoryMock,
            $this->contextAnalyzerMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Тест выбора типа действия для утра с хорошей погодой
     */
    public function test_recommend_morning_good_weather_returns_outdoor_activity(): void
    {
        $context = $this->createContext('MORNING', 'SUMMER', 'CLEAR', 22);

        $this->contextAnalyzerMock
            ->shouldReceive('isGoodForIndoorActivity')
            ->with($context)
            ->andReturn(false);

        $this->contextAnalyzerMock
            ->shouldReceive('hasNearbyPlaces')
            ->with($context)
            ->andReturn(true);

        $this->contextAnalyzerMock
            ->shouldReceive('getContextAnalysis')
            ->with($context)
            ->andReturn(['season' => 'SUMMER']);

        $this->contextAnalyzerMock
            ->shouldReceive('getNearbyPlaces')
            ->with($context, 3)
            ->andReturn([]);

        $action = Action::factory()->visitPlace()->make(['id' => 5]);

        $this->actionRepositoryMock
            ->shouldReceive('getByType')
            ->with('visit_place')
            ->andReturn(new Collection([$action]));

        $recommendation = Recommendation::factory()->make([
            'id' => 1,
            'user_id' => 123,
            'action_type' => 'visit_place',
            'action_id' => 5,
            'created_at' => now(),
        ]);

        $this->recommendationRepositoryMock
            ->shouldReceive('create')
            ->andReturn($recommendation);

        $result = $this->service->recommend($context);

        $this->assertEquals('123', $result->userId);
        $this->assertEquals('visit_place', $result->actionType);
    }

    /**
     * Тест выбора типа действия для ночи
     */
    public function test_recommend_night_returns_sleep(): void
    {
        $context = $this->createContext('NIGHT', 'WINTER', 'CLEAR', -5);

        $this->contextAnalyzerMock
            ->shouldReceive('isGoodForIndoorActivity')
            ->with($context)
            ->andReturn(true);

        $this->contextAnalyzerMock
            ->shouldReceive('getContextAnalysis')
            ->with($context)
            ->andReturn(['season' => 'WINTER']);

        $this->contextAnalyzerMock
            ->shouldReceive('isHot')
            ->with($context)
            ->andReturn(false);

        $this->contextAnalyzerMock
            ->shouldReceive('isCold')
            ->with($context)
            ->andReturn(true);

        $action = Action::factory()->sleep()->make(['id' => 2]);

        $this->actionRepositoryMock
            ->shouldReceive('getByType')
            ->with('sleep')
            ->andReturn(new Collection([$action]));

        $recommendation = Recommendation::factory()->make([
            'id' => 2,
            'user_id' => 123,
            'action_type' => 'sleep',
            'action_id' => 2,
            'created_at' => now(),
        ]);

        $this->recommendationRepositoryMock
            ->shouldReceive('create')
            ->andReturn($recommendation);

        $result = $this->service->recommend($context);

        $this->assertEquals('sleep', $result->actionType);
    }

    /**
     * Тест выбора типа действия для дождливой погоды
     */
    public function test_recommend_rainy_weather_returns_movie(): void
    {
        $context = $this->createContext('AFTERNOON', 'AUTUMN', 'RAINY', 10);

        $this->contextAnalyzerMock
            ->shouldReceive('isGoodForIndoorActivity')
            ->with($context)
            ->andReturn(true);

        $this->contextAnalyzerMock
            ->shouldReceive('getContextAnalysis')
            ->with($context)
            ->andReturn(['season' => 'AUTUMN']);

        $this->contextAnalyzerMock
            ->shouldReceive('getRecommendedMovieGenre')
            ->with($context)
            ->andReturn('драма');

        $action = Action::factory()->watchMovie()->make(['id' => 3]);

        $this->actionRepositoryMock
            ->shouldReceive('getByType')
            ->with('watch_movie')
            ->andReturn(new Collection([$action]));

        $recommendation = Recommendation::factory()->make([
            'id' => 3,
            'user_id' => 123,
            'action_type' => 'watch_movie',
            'action_id' => 3,
            'created_at' => now(),
        ]);

        $this->recommendationRepositoryMock
            ->shouldReceive('create')
            ->andReturn($recommendation);

        $result = $this->service->recommend($context);

        $this->assertEquals('watch_movie', $result->actionType);
    }

    /**
     * Тест получения истории рекомендаций
     */
    public function test_get_user_history(): void
    {
        $recommendations = Recommendation::factory()->forUser(123)->count(3)->make(['id' => 1]);
        $collection = new Collection($recommendations);

        $this->recommendationRepositoryMock
            ->shouldReceive('getByUser')
            ->with(123, 20)
            ->once()
            ->andReturn($collection);

        $history = $this->service->getUserHistory(123);

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
