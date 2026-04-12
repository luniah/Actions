<?php

namespace App\Application\Services;

use App\Application\Models\WorldContextModel;
use App\Application\Models\RecommendationModel;
use App\Domain\Repositories\RecommendationRepository;
use App\Domain\Repositories\ActionRepository;
use Illuminate\Support\Facades\Storage;

class RecommendationService
{
    private array $musicData;
    private array $moviesData;

    public function __construct(
        private readonly RecommendationRepository $recommendationRepository,
        private readonly ActionRepository $actionRepository,
        private readonly WorldContextAnalyzer $contextAnalyzer,
    ) {
        $musicPath = storage_path('app/data/music.json');
        $moviesPath = storage_path('app/data/movies.json');

        $this->musicData = file_exists($musicPath)
            ? json_decode(file_get_contents($musicPath), true)
            : ['tracks' => []];

        $this->moviesData = file_exists($moviesPath)
            ? json_decode(file_get_contents($moviesPath), true)
            : ['movies' => []];
    }

    /**
     * Получить рекомендацию действия на основе контекста мира
     */
    public function recommend(WorldContextModel $context): RecommendationModel
    {
        $actionType = $this->chooseActionType($context);
        $action = $this->actionRepository->getByType($actionType)->first();
        $suggestions = $this->getSuggestionsForType($actionType, $context);
        $contextAnalysis = $this->contextAnalyzer->getContextAnalysis($context);

        $recommendation = $this->recommendationRepository->create([
            'user_id' => $context->userId,
            'action_type' => $actionType,
            'action_id' => $action?->id,
            'context' => $contextAnalysis,
            'suggestions' => $suggestions,
        ]);

        return RecommendationModel::fromEntity($recommendation);
    }

    /**
     * Выбрать тип действия на основе контекста
     */
    private function chooseActionType(WorldContextModel $context): string
    {
        if ($this->contextAnalyzer->isGoodForIndoorActivity($context)) {
            if ($context->dayTime === 'NIGHT') {
                return 'sleep';
            }
            if ($context->dayTime === 'EVENING') {
                return rand(0, 1) === 0 ? 'watch_movie' : 'listen_music';
            }
            return 'watch_movie';
        }

        if ($this->contextAnalyzer->hasNearbyPlaces($context)) {
            return 'visit_place';
        }

        return 'walk';
    }

    /**
     * Получить конкретные предложения для типа действия
     */
    private function getSuggestionsForType(string $type, WorldContextModel $context): array
    {
        return match ($type) {
            'watch_movie' => $this->getMovieSuggestions($context),
            'listen_music' => $this->getMusicSuggestions($context),
            'visit_place' => $this->getPlaceSuggestions($context),
            'walk' => $this->getWalkSuggestions($context),
            'sleep' => $this->getSleepSuggestions($context),
            default => [],
        };
    }

    /**
     * Получить предложения музыки из JSON-файла
     */
    private function getMusicSuggestions(WorldContextModel $context): array
    {
        $tracks = $this->musicData['tracks'] ?? [];

        if (empty($tracks)) {
            return $this->getStaticMusicFallback($context);
        }

        $scoredTracks = array_map(function ($track) use ($context) {
            $score = $this->calculateMusicScore($track, $context);
            $track['score'] = $score;
            $track['reason'] = $this->generateMusicReason($track, $context);
            return $track;
        }, $tracks);

        usort($scoredTracks, fn($a, $b) => $b['score'] <=> $a['score']);

        $topTracks = array_slice($scoredTracks, 0, 3);

        return array_map(function ($track) {
            return [
                'type' => 'music',
                'id' => $track['id'],
                'name' => $track['name'],
                'artist' => $track['artist'],
                'album' => $track['album'],
                'duration' => $track['duration'],
                'genre' => $track['genre'],
                'mood' => $track['mood'],
                'reason' => $track['reason'],
            ];
        }, $topTracks);
    }

    /**
     * Подсчёт очков релевантности трека
     */
    private function calculateMusicScore(array $track, WorldContextModel $context): int
    {
        $score = 0;
        $mood = $track['mood'] ?? [];

        if ($context->dayTime === 'MORNING' && (in_array('energetic', $mood) || in_array('happy', $mood))) $score += 5;
        if ($context->dayTime === 'AFTERNOON' && in_array('energetic', $mood)) $score += 3;
        if ($context->dayTime === 'EVENING' && (in_array('calm', $mood) || in_array('romantic', $mood))) $score += 5;
        if ($context->dayTime === 'NIGHT' && in_array('calm', $mood)) $score += 5;

        $condition = $context->weather['condition'] ?? '';
        if ($condition === 'RAINY' && (in_array('calm', $mood) || in_array('melancholic', $mood))) $score += 4;
        if ($condition === 'SNOWY' && in_array('cozy', $mood)) $score += 4;
        if ($condition === 'CLEAR' && $context->dayTime === 'AFTERNOON' && in_array('happy', $mood)) $score += 3;

        if ($context->season === 'WINTER' && (in_array('cozy', $mood) || in_array('nostalgic', $mood))) $score += 3;
        if ($context->season === 'SUMMER' && (in_array('dance', $mood) || in_array('happy', $mood))) $score += 3;
        if ($context->season === 'SPRING' && in_array('romantic', $mood)) $score += 2;
        if ($context->season === 'AUTUMN' && in_array('melancholic', $mood)) $score += 2;

        return $score;
    }

    /**
     * Сгенерировать причину рекомендации
     */
    private function generateMusicReason(array $track, WorldContextModel $context): string
    {
        $mood = $track['mood'] ?? [];
        $genre = $track['genre'] ?? '';

        if ($context->dayTime === 'MORNING' && (in_array('energetic', $mood) || in_array('happy', $mood))) {
            return 'Бодрый трек для отличного начала дня';
        }
        if ($context->dayTime === 'EVENING' && in_array('calm', $mood)) {
            return 'Спокойная музыка для расслабленного вечера';
        }
        if ($context->dayTime === 'NIGHT' && in_array('calm', $mood)) {
            return 'Умиротворяющая композиция для ночного отдыха';
        }
        if ($context->weather['condition'] === 'RAINY' && in_array('melancholic', $mood)) {
            return 'Атмосферный трек для дождливого настроения';
        }
        if ($context->season === 'WINTER' && in_array('nostalgic', $mood)) {
            return 'Уютная зимняя классика';
        }
        if ($context->season === 'SUMMER' && in_array('dance', $mood)) {
            return 'Летний танцевальный хит';
        }

        return "Рекомендовано в жанре {$genre}";
    }

    /**
     * Получить предложения фильмов из JSON-файла
     */
    private function getMovieSuggestions(WorldContextModel $context): array
    {
        $movies = $this->moviesData['movies'] ?? [];

        if (empty($movies)) {
            return $this->getStaticMovieFallback($context);
        }

        $scoredMovies = array_map(function ($movie) use ($context) {
            $score = $this->calculateMovieScore($movie, $context);
            $movie['score'] = $score;
            $movie['reason'] = $this->generateMovieReason($movie, $context);
            return $movie;
        }, $movies);

        usort($scoredMovies, fn($a, $b) => $b['score'] <=> $a['score']);

        $topMovies = array_slice($scoredMovies, 0, 3);

        return array_map(function ($movie) {
            return [
                'type' => 'movie',
                'id' => $movie['id'],
                'title' => $movie['title'],
                'description' => $movie['description'],
                'genres' => $movie['genres'],
                'director' => $movie['director'],
                'year' => $movie['year'],
                'duration' => $movie['duration'],
                'rating' => $movie['rating'],
                'reason' => $movie['reason'],
            ];
        }, $topMovies);
    }

    /**
     * Подсчёт очков релевантности фильма
     */
    private function calculateMovieScore(array $movie, WorldContextModel $context): int
    {
        $score = 0;
        $mood = $movie['mood'] ?? [];
        $genres = $movie['genres'] ?? [];

        $condition = $context->weather['condition'] ?? '';
        if ($condition === 'RAINY' && (in_array('cozy', $mood) || in_array('atmospheric', $mood))) $score += 5;
        if ($condition === 'SNOWY' && (in_array('новогодний', $genres) || in_array('cozy', $mood))) $score += 5;

        if ($context->dayTime === 'EVENING' && (in_array('epic', $mood) || in_array('emotional', $mood))) $score += 4;
        if ($context->dayTime === 'NIGHT' && in_array('dark', $mood)) $score += 4;
        if ($context->dayTime === 'AFTERNOON' && in_array('funny', $mood)) $score += 3;

        if ($context->season === 'WINTER' && in_array('новогодний', $genres)) $score += 5;
        if ($context->season === 'SUMMER' && in_array('приключения', $genres)) $score += 3;
        if ($context->season === 'AUTUMN' && in_array('atmospheric', $mood)) $score += 3;

        $score += ($movie['rating'] ?? 7) * 1;

        return (int) $score;
    }

    /**
     * Сгенерировать причину рекомендации фильма
     */
    private function generateMovieReason(array $movie, WorldContextModel $context): string
    {
        $mood = $movie['mood'] ?? [];
        $genres = $movie['genres'] ?? [];

        if ($context->weather['condition'] === 'RAINY' && in_array('cozy', $mood)) {
            return 'Уютный фильм для дождливого вечера';
        }
        if ($context->season === 'WINTER' && in_array('новогодний', $genres)) {
            return 'Новогоднее настроение для зимнего вечера';
        }
        if ($context->dayTime === 'EVENING' && in_array('epic', $mood)) {
            return 'Эпическое кино для вечернего просмотра';
        }
        if ($context->dayTime === 'NIGHT' && in_array('dark', $mood)) {
            return 'Атмосферный фильм для ночного просмотра';
        }

        $genre = $genres[0] ?? 'драма';
        return "Рекомендовано в жанре {$genre}";
    }

    /**
     * Получить предложения мест
     */
    private function getPlaceSuggestions(WorldContextModel $context): array
    {
        $places = $this->contextAnalyzer->getNearbyPlaces($context, 3);

        if (empty($places)) {
            return [];
        }

        return array_map(function ($place) {
            return [
                'type' => 'place',
                'id' => $place['id'],
                'name' => $place['name'],
                'category' => $place['category'],
                'address' => $place['address'] ?? null,
                'rating' => $place['rating'] ?? null,
                'reason' => 'Находится рядом с вами',
            ];
        }, $places);
    }

    /**
     * Получить предложения для прогулки
     */
    private function getWalkSuggestions(WorldContextModel $context): array
    {
        $suggestions = [];

        $clothing = $this->contextAnalyzer->getClothingRecommendation($context);
        $suggestions[] = ['type' => 'tip', 'message' => $clothing];

        if ($this->contextAnalyzer->isCold($context)) {
            $suggestions[] = ['type' => 'tip', 'message' => 'Не забудьте взять горячий напиток с собой'];
        }
        if ($this->contextAnalyzer->isHot($context)) {
            $suggestions[] = ['type' => 'tip', 'message' => 'Возьмите с собой воду'];
        }
        if ($this->contextAnalyzer->isRainy($context)) {
            $suggestions[] = ['type' => 'tip', 'message' => 'Не забудьте зонт'];
        }

        return $suggestions;
    }

    /**
     * Получить предложения для сна
     */
    private function getSleepSuggestions(WorldContextModel $context): array
    {
        $suggestions = [
            ['type' => 'tip', 'message' => 'Проветрите комнату перед сном'],
        ];

        if ($this->contextAnalyzer->isHot($context)) {
            $suggestions[] = ['type' => 'tip', 'message' => 'Включите кондиционер или вентилятор для комфортного сна'];
        }
        if ($this->contextAnalyzer->isCold($context)) {
            $suggestions[] = ['type' => 'tip', 'message' => 'Укройтесь тёплым одеялом'];
        }

        $suggestions[] = ['type' => 'tip', 'message' => 'Постарайтесь лечь спать до полуночи для лучшего отдыха'];

        return $suggestions;
    }

    /**
     * Статические треки на случай отсутствия данных
     */
    private function getStaticMusicFallback(WorldContextModel $context): array
    {
        return [
            ['type' => 'music', 'id' => 'fallback_1', 'name' => 'Новогодняя', 'artist' => 'Дискотека Авария', 'reason' => 'Зимнее настроение'],
            ['type' => 'music', 'id' => 'fallback_2', 'name' => 'Снег', 'artist' => 'Филипп Киркоров', 'reason' => 'Зимняя классика'],
        ];
    }

    /**
     * Статические фильмы на случай отсутствия данных
     */
    private function getStaticMovieFallback(WorldContextModel $context): array
    {
        return [
            ['type' => 'movie', 'id' => 'fallback_1', 'title' => 'Один дома', 'reason' => 'Новогодняя классика'],
            ['type' => 'movie', 'id' => 'fallback_2', 'title' => 'Ирония судьбы', 'reason' => 'Традиционный новогодний фильм'],
        ];
    }

    /**
     * Получить историю рекомендаций пользователя
     */
    public function getUserHistory(int $userId, int $limit = 20): array
    {
        $recommendations = $this->recommendationRepository->getByUser($userId, $limit);

        return $recommendations->map(function ($rec) {
            return RecommendationModel::fromEntity($rec)->toArray();
        })->toArray();
    }
}
