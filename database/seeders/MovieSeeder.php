<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MovieSeeder extends Seeder
{
    /**
     * Заполнение таблицы фильмов начальными данными
     */
    public function run(): void
    {
        $movies = [
            [
                'title' => 'Один дома',
                'description' => 'Рождественская комедия о мальчике, которого случайно забыли дома',
                'genres' => json_encode(['комедия', 'семейный', 'новогодний']),
                'release_year' => 1990,
                'duration' => 103,
                'director' => 'Крис Коламбус',
                'poster_url' => 'https://example.com/home_alone.jpg',
                'external_id' => 'tmdb_1',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Гринч - похититель Рождества',
                'description' => 'Зеленый отшельник решает украсть Рождество у жителей Ктограда',
                'genres' => json_encode(['комедия', 'семейный', 'новогодний']),
                'release_year' => 2000,
                'duration' => 104,
                'director' => 'Рон Ховард',
                'poster_url' => 'https://example.com/grinch.jpg',
                'external_id' => 'tmdb_2',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Реальная любовь',
                'description' => 'Десять историй о любви в канун Рождества',
                'genres' => json_encode(['мелодрама', 'комедия', 'новогодний']),
                'release_year' => 2003,
                'duration' => 135,
                'director' => 'Ричард Кёртис',
                'poster_url' => 'https://example.com/love_actually.jpg',
                'external_id' => 'tmdb_3',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Лето в городе',
                'description' => 'Романтическая комедия о летних приключениях',
                'genres' => json_encode(['комедия', 'мелодрама']),
                'release_year' => 2019,
                'duration' => 95,
                'director' => 'Анна Меликян',
                'poster_url' => null,
                'external_id' => 'tmdb_4',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Прогулка',
                'description' => 'Драма о путешествии по парку',
                'genres' => json_encode(['драма']),
                'release_year' => 2021,
                'duration' => 110,
                'director' => 'Алексей Учитель',
                'poster_url' => null,
                'external_id' => 'tmdb_5',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Побег из Шоушенка',
                'description' => 'История банкира, осуждённого за убийство, которое он не совершал',
                'genres' => json_encode(['драма']),
                'release_year' => 1994,
                'duration' => 142,
                'director' => 'Фрэнк Дарабонт',
                'poster_url' => null,
                'external_id' => 'tmdb_6',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Крёстный отец',
                'description' => 'Эпическая сага о мафиозной семье Корлеоне',
                'genres' => json_encode(['драма', 'криминал']),
                'release_year' => 1972,
                'duration' => 175,
                'director' => 'Фрэнсис Форд Коппола',
                'poster_url' => null,
                'external_id' => 'tmdb_7',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Тёмный рыцарь',
                'description' => 'Бэтмен противостоит Джокеру, сеющему хаос в Готэме',
                'genres' => json_encode(['боевик', 'драма', 'криминал']),
                'release_year' => 2008,
                'duration' => 152,
                'director' => 'Кристофер Нолан',
                'poster_url' => null,
                'external_id' => 'tmdb_8',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Криминальное чтиво',
                'description' => 'Несколько переплетающихся историй о преступниках Лос-Анджелеса',
                'genres' => json_encode(['криминал', 'драма']),
                'release_year' => 1994,
                'duration' => 154,
                'director' => 'Квентин Тарантино',
                'poster_url' => null,
                'external_id' => 'tmdb_9',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Начало',
                'description' => 'Вор, способный проникать в сны, получает задание внедрить идею',
                'genres' => json_encode(['фантастика', 'боевик', 'триллер']),
                'release_year' => 2010,
                'duration' => 148,
                'director' => 'Кристофер Нолан',
                'poster_url' => null,
                'external_id' => 'tmdb_10',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($movies as $movie) {
            DB::table('actions_service.movies')->updateOrInsert(
                ['external_id' => $movie['external_id']],
                $movie
            );
        }
    }
}
