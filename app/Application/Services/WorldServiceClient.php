<?php

namespace App\Application\Services;

use App\Application\Models\WorldContextModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class WorldServiceClient
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.world.url', env('WORLD_SERVICE_URL', 'http://localhost:8001'));
        $this->apiKey = config('services.world.api_key', env('WORLD_SERVICE_API_KEY', ''));
    }

    /**
     * Получить контекст окружающего мира для пользователя
     */
    public function getWorldContext(int $userId, ?float $latitude, ?float $longitude): WorldContextModel
    {
        // Заглушка для тестирования, пока сервис World недоступен
        if (env('APP_ENV') === 'local' && env('USE_MOCK_WORLD', true)) {
            return $this->getMockWorldContext($userId, $latitude, $longitude);
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->post("{$this->baseUrl}/api/v1/world/current", [
            'userId' => $userId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch world data: ' . $response->body());
        }

        $data = $response->json();

        // Добавляем координаты в ответ, если их нет
        $data['latitude'] = $latitude;
        $data['longitude'] = $longitude;

        return WorldContextModel::fromArray($data);
    }

    /**
     * Заглушка для тестирования
     */
    private function getMockWorldContext(int $userId, ?float $latitude, ?float $longitude): WorldContextModel
    {
        // Определяем сезон по месяцу
        $month = (int) date('m');
        $season = match (true) {
            $month >= 3 && $month <= 5 => 'SPRING',
            $month >= 6 && $month <= 8 => 'SUMMER',
            $month >= 9 && $month <= 11 => 'AUTUMN',
            default => 'WINTER',
        };

        // Определяем время суток
        $hour = (int) date('H');
        $dayTime = match (true) {
            $hour >= 5 && $hour < 12 => 'MORNING',
            $hour >= 12 && $hour < 17 => 'AFTERNOON',
            $hour >= 17 && $hour < 22 => 'EVENING',
            default => 'NIGHT',
        };

        // Тестовые развлекательные места
        $entertainment = [
            'nearby_places' => [
                [
                    'id' => 1,
                    'name' => 'Центральный парк',
                    'category' => 'park',
                    'address' => 'ул. Парковая, 1',
                    'rating' => 4.5,
                ],
                [
                    'id' => 2,
                    'name' => 'Кинотеатр "Премьера"',
                    'category' => 'cinema',
                    'address' => 'ул. Киношная, 10',
                    'rating' => 4.2,
                ],
                [
                    'id' => 3,
                    'name' => 'Ресторан "Вкусно"',
                    'category' => 'restaurant',
                    'address' => 'пр. Вкусный, 5',
                    'rating' => 4.7,
                ],
            ],
            'places_by_category' => [
                'restaurant' => [
                    [
                        'id' => 3,
                        'name' => 'Ресторан "Вкусно"',
                        'category' => 'restaurant',
                    ]
                ],
                'cinema' => [
                    [
                        'id' => 2,
                        'name' => 'Кинотеатр "Премьера"',
                        'category' => 'cinema',
                    ]
                ],
                'park' => [
                    [
                        'id' => 1,
                        'name' => 'Центральный парк',
                        'category' => 'park',
                    ]
                ],
            ],
        ];

        return new WorldContextModel(
            userId: (string) $userId,
            latitude: $latitude,
            longitude: $longitude,
            dayTime: $dayTime,
            season: $season,
            weather: [
                'temperature' => $season === 'WINTER' ? -5 : 20,
                'feelsLike' => $season === 'WINTER' ? -8 : 18,
                'condition' => $season === 'WINTER' ? 'SNOWY' : 'CLEAR',
                'humidity' => 70,
                'windSpeed' => 5.0,
            ],
            entertainment: $entertainment,
            sunrise: '07:00',
            sunset: $season === 'WINTER' ? '17:00' : '21:00',
            updatedAt: date('c'),
        );
    }
}
