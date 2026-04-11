<?php

namespace App\Application\Services;

use App\Application\Models\WorldContextModel;

class WorldContextAnalyzer
{
    // Погодные условия, неподходящие для прогулки
    private const BAD_WALK_CONDITIONS = ['RAINY', 'SNOWY', 'STORMY'];

    // Тёмное время суток
    private const DARK_DAYTIMES = ['EVENING', 'NIGHT'];

    // Светлое время суток
    private const LIGHT_DAYTIMES = ['MORNING', 'AFTERNOON'];

    /**
     * Проверить, подходит ли погода для прогулки
     */
    public function isGoodWeatherForWalk(WorldContextModel $context): bool
    {
        $condition = $context->weather['condition'] ?? '';
        return !in_array($condition, self::BAD_WALK_CONDITIONS);
    }

    /**
     * Проверить, тёмное ли время суток
     */
    public function isDarkTime(WorldContextModel $context): bool
    {
        return in_array($context->dayTime, self::DARK_DAYTIMES);
    }

    /**
     * Проверить, светлое ли время суток
     */
    public function isLightTime(WorldContextModel $context): bool
    {
        return in_array($context->dayTime, self::LIGHT_DAYTIMES);
    }

    /**
     * Получить температуру
     */
    public function getTemperature(WorldContextModel $context): ?float
    {
        return $context->weather['temperature'] ?? null;
    }

    /**
     * Получить ощущаемую температуру
     */
    public function getFeelsLikeTemperature(WorldContextModel $context): ?float
    {
        return $context->weather['feelsLike'] ?? null;
    }

    /**
     * Получить состояние погоды
     */
    public function getCondition(WorldContextModel $context): ?string
    {
        return $context->weather['condition'] ?? null;
    }

    /**
     * Получить влажность
     */
    public function getHumidity(WorldContextModel $context): ?int
    {
        return $context->weather['humidity'] ?? null;
    }

    /**
     * Получить скорость ветра
     */
    public function getWindSpeed(WorldContextModel $context): ?float
    {
        return $context->weather['windSpeed'] ?? null;
    }

    /**
     * Проверить, есть ли поблизости развлекательные места
     */
    public function hasNearbyPlaces(WorldContextModel $context): bool
    {
        return !empty($context->entertainment['nearby_places']);
    }

    /**
     * Получить ближайшие места
     */
    public function getNearbyPlaces(WorldContextModel $context, int $limit = 5): array
    {
        return array_slice($context->entertainment['nearby_places'] ?? [], 0, $limit);
    }

    /**
     * Получить количество ближайших мест
     */
    public function getNearbyPlacesCount(WorldContextModel $context): int
    {
        return count($context->entertainment['nearby_places'] ?? []);
    }

    /**
     * Получить места по категориям
     */
    public function getPlacesByCategory(WorldContextModel $context, string $category): array
    {
        return $context->entertainment['places_by_category'][$category] ?? [];
    }

    /**
     * Получить все доступные категории мест
     */
    public function getAvailablePlaceCategories(WorldContextModel $context): array
    {
        return array_keys($context->entertainment['places_by_category'] ?? []);
    }

    /**
     * Определить, холодно ли сейчас
     */
    public function isCold(WorldContextModel $context): bool
    {
        $temp = $this->getTemperature($context);
        return $temp !== null && $temp < 10;
    }

    /**
     * Определить, очень ли холодно
     */
    public function isVeryCold(WorldContextModel $context): bool
    {
        $temp = $this->getTemperature($context);
        return $temp !== null && $temp < 0;
    }

    /**
     * Определить, жарко ли сейчас
     */
    public function isHot(WorldContextModel $context): bool
    {
        $temp = $this->getTemperature($context);
        return $temp !== null && $temp > 25;
    }

    /**
     * Определить, комфортная ли температура
     */
    public function isComfortableTemperature(WorldContextModel $context): bool
    {
        $temp = $this->getTemperature($context);
        return $temp !== null && $temp >= 10 && $temp <= 25;
    }

    /**
     * Проверить, идёт ли дождь
     */
    public function isRainy(WorldContextModel $context): bool
    {
        return $this->getCondition($context) === 'RAINY';
    }

    /**
     * Проверить, идёт ли снег
     */
    public function isSnowy(WorldContextModel $context): bool
    {
        return $this->getCondition($context) === 'SNOWY';
    }

    /**
     * Проверить, ясная ли погода
     */
    public function isClear(WorldContextModel $context): bool
    {
        return $this->getCondition($context) === 'CLEAR';
    }

    /**
     * Получить рекомендацию по одежде на основе температуры
     */
    public function getClothingRecommendation(WorldContextModel $context): string
    {
        $temp = $this->getTemperature($context);

        if ($temp === null) {
            return 'Одевайтесь по погоде';
        }

        return match (true) {
            $temp < -10 => 'Очень холодно, одевайтесь максимально тепло',
            $temp < 0 => 'Морозно, нужна тёплая куртка, шапка и перчатки',
            $temp < 10 => 'Прохладно, рекомендуем надеть куртку',
            $temp < 15 => 'Свежо, подойдёт лёгкая куртка или кофта',
            $temp < 20 => 'Комфортно, можно в футболке и лёгкой кофте',
            $temp < 25 => 'Тепло, подойдёт футболка',
            default => 'Жарко, одевайтесь максимально легко',
        };
    }

    /**
     * Определить подходящий жанр фильма по контексту
     */
    public function getRecommendedMovieGenre(WorldContextModel $context): string
    {
        // Если плохая погода - что-то уютное
        if (!$this->isGoodWeatherForWalk($context)) {
            return match ($context->season) {
                'WINTER' => 'семейный',
                'AUTUMN' => 'драма',
                default => 'комедия',
            };
        }

        // Если вечер или ночь - что-то спокойное
        if ($this->isDarkTime($context)) {
            return 'мелодрама';
        }

        // По сезону
        return match ($context->season) {
            'WINTER' => 'новогодний',
            'SUMMER' => 'приключения',
            'SPRING' => 'романтика',
            'AUTUMN' => 'детектив',
            default => 'боевик',
        };
    }

    /**
     * Определить подходящее настроение для музыки по контексту
     */
    public function getRecommendedMusicMood(WorldContextModel $context): string
    {
        // По времени суток
        $moodByDaytime = [
            'MORNING' => 'energetic',
            'AFTERNOON' => 'happy',
            'EVENING' => 'chill',
            'NIGHT' => 'relax',
        ];

        $mood = $moodByDaytime[$context->dayTime] ?? 'pop';

        // Корректировка по погоде
        if ($this->isRainy($context)) {
            $mood = 'sad';
        } elseif ($this->isSnowy($context) && $context->season === 'WINTER') {
            $mood = 'christmas';
        } elseif ($this->isClear($context) && $context->season === 'SUMMER') {
            $mood = 'summer';
        }

        return $mood;
    }

    /**
     * Получить текущий час
     */
    public function getCurrentHour(WorldContextModel $context): ?int
    {
        return (int) date('H', strtotime($context->updatedAt));
    }

    /**
     * Проверить, сейчас день или ночь по астрономическому времени
     */
    public function isAstronomicalDay(WorldContextModel $context): bool
    {
        $currentHour = $this->getCurrentHour($context);

        if ($currentHour === null) {
            return $this->isLightTime($context);
        }

        $sunriseHour = (int) substr($context->sunrise, 0, 2);
        $sunsetHour = (int) substr($context->sunset, 0, 2);

        return $currentHour >= $sunriseHour && $currentHour < $sunsetHour;
    }

    /**
     * Определить, подходит ли время для активного отдыха
     */
    public function isGoodForOutdoorActivity(WorldContextModel $context): bool
    {
        return $this->isGoodWeatherForWalk($context)
            && $this->isLightTime($context)
            && $this->isComfortableTemperature($context);
    }

    /**
     * Определить, подходит ли время для домашнего отдыха
     */
    public function isGoodForIndoorActivity(WorldContextModel $context): bool
    {
        return !$this->isGoodWeatherForWalk($context)
            || $this->isDarkTime($context)
            || $this->isVeryCold($context)
            || $this->isHot($context);
    }

    /**
     * Получить полный анализ контекста в виде массива
     */
    public function getContextAnalysis(WorldContextModel $context): array
    {
        return [
            'season' => $context->season,
            'day_time' => $context->dayTime,
            'weather_condition' => $this->getCondition($context),
            'temperature' => $this->getTemperature($context),
            'feels_like' => $this->getFeelsLikeTemperature($context),
            'humidity' => $this->getHumidity($context),
            'wind_speed' => $this->getWindSpeed($context),
            'is_dark' => $this->isDarkTime($context),
            'is_light' => $this->isLightTime($context),
            'is_good_for_walk' => $this->isGoodWeatherForWalk($context),
            'is_cold' => $this->isCold($context),
            'is_very_cold' => $this->isVeryCold($context),
            'is_hot' => $this->isHot($context),
            'is_rainy' => $this->isRainy($context),
            'is_snowy' => $this->isSnowy($context),
            'is_clear' => $this->isClear($context),
            'has_nearby_places' => $this->hasNearbyPlaces($context),
            'nearby_places_count' => $this->getNearbyPlacesCount($context),
            'available_place_categories' => $this->getAvailablePlaceCategories($context),
            'is_good_for_outdoor' => $this->isGoodForOutdoorActivity($context),
            'is_good_for_indoor' => $this->isGoodForIndoorActivity($context),
            'clothing_recommendation' => $this->getClothingRecommendation($context),
            'recommended_movie_genre' => $this->getRecommendedMovieGenre($context),
            'recommended_music_mood' => $this->getRecommendedMusicMood($context),
        ];
    }
}
