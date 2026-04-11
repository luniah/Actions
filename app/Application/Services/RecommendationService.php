<?php

namespace App\Application\Services;

use App\Application\Models\WorldContextModel;
use App\Application\Models\RecommendationModel;
use App\Domain\Repositories\RecommendationRepository;
use App\Domain\Repositories\ActionRepository;
use App\Domain\Repositories\SongRepository;
use App\Domain\Repositories\MovieRepository;

class RecommendationService
{
    public function __construct(
        private readonly RecommendationRepository $recommendationRepository,
        private readonly ActionRepository $actionRepository,
        private readonly SongRepository $songRepository,
        private readonly MovieRepository $movieRepository,
        private readonly WorldContextAnalyzer $contextAnalyzer,
    ) {}

    /**
     * Получить рекомендацию действия на основе контекста мира
     */
    public function recommend(WorldContextModel $context): RecommendationModel
    {
        // Выбираем тип действия на основе анализа контекста
        $actionType = $this->chooseActionType($context);

        // Получаем само действие
        $action = $this->actionRepository->getByType($actionType)->first();

        // Получаем конкретные предложения
        $suggestions = $this->getSuggestionsForType($actionType, $context);

        // Получаем полный анализ контекста для сохранения
        $contextAnalysis = $this->contextAnalyzer->getContextAnalysis($context);

        // Сохраняем рекомендацию в БД
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
        // Если подходит для домашнего отдыха - смотреть фильм
        if ($this->contextAnalyzer->isGoodForIndoorActivity($context)) {
            // Если ночь - спать
            if ($context->dayTime === 'NIGHT') {
                return 'sleep';
            }

            // Если вечер - слушать музыку или смотреть фильм
            if ($context->dayTime === 'EVENING') {
                // Чередуем для разнообразия (заглушка, позже будет LLM)
                return rand(0, 1) === 0 ? 'watch_movie' : 'listen_music';
            }

            return 'watch_movie';
        }

        // Если есть места поблизости - посетить
        if ($this->contextAnalyzer->hasNearbyPlaces($context)) {
            return 'visit_place';
        }

        // По умолчанию - прогулка
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
     * Получить предложения фильмов
     */
    private function getMovieSuggestions(WorldContextModel $context): array
    {
        $genre = $this->contextAnalyzer->getRecommendedMovieGenre($context);

        $movies = $this->movieRepository->getByGenre($genre);

        if ($movies->isEmpty()) {
            $movies = $this->movieRepository->getAll()->take(3);
        }

        return $movies->map(function ($movie) use ($genre) {
            return [
                'type' => 'movie',
                'id' => $movie->id,
                'title' => $movie->title,
                'genres' => $movie->genres,
                'reason' => "Подходит под жанр: {$genre}",
            ];
        })->take(3)->values()->toArray();
    }

    /**
     * Получить предложения музыки
     */
    private function getMusicSuggestions(WorldContextModel $context): array
    {
        $mood = $this->contextAnalyzer->getRecommendedMusicMood($context);

        $songs = $this->songRepository->getByTag($mood);

        if ($songs->isEmpty()) {
            $songs = $this->songRepository->getAll()->take(3);
        }

        return $songs->map(function ($song) use ($mood) {
            return [
                'type' => 'song',
                'id' => $song->id,
                'name' => $song->name,
                'artist' => $song->artist,
                'reason' => "Под настроение: {$mood}",
            ];
        })->take(3)->values()->toArray();
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

        // Рекомендация по одежде
        $clothing = $this->contextAnalyzer->getClothingRecommendation($context);
        $suggestions[] = [
            'type' => 'tip',
            'message' => $clothing,
        ];

        // Дополнительные советы
        if ($this->contextAnalyzer->isCold($context)) {
            $suggestions[] = [
                'type' => 'tip',
                'message' => 'Не забудьте взять горячий напиток с собой',
            ];
        }

        if ($this->contextAnalyzer->isHot($context)) {
            $suggestions[] = [
                'type' => 'tip',
                'message' => 'Возьмите с собой воду',
            ];
        }

        if ($this->contextAnalyzer->isRainy($context)) {
            $suggestions[] = [
                'type' => 'tip',
                'message' => 'Не забудьте зонт',
            ];
        }

        return $suggestions;
    }

    /**
     * Получить предложения для сна
     */
    private function getSleepSuggestions(WorldContextModel $context): array
    {
        $suggestions = [
            [
                'type' => 'tip',
                'message' => 'Проветрите комнату перед сном',
            ]
        ];

        if ($this->contextAnalyzer->isHot($context)) {
            $suggestions[] = [
                'type' => 'tip',
                'message' => 'Включите кондиционер или вентилятор для комфортного сна',
            ];
        }

        if ($this->contextAnalyzer->isCold($context)) {
            $suggestions[] = [
                'type' => 'tip',
                'message' => 'Укройтесь тёплым одеялом',
            ];
        }

        return $suggestions;
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
