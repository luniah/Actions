<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActionSeeder extends Seeder
{
    /**
     * Заполнение таблицы действий начальными данными
     */
    public function run(): void
    {
        $actions = [
            [
                'type' => 'walk',
                'name' => 'Прогулка',
                'description' => 'Прогулка на свежем воздухе',
                'metadata' => json_encode(['icon' => '🚶']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'sleep',
                'name' => 'Сон',
                'description' => 'Полноценный отдых и восстановление сил',
                'metadata' => json_encode(['icon' => '😴']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'watch_movie',
                'name' => 'Просмотр фильма',
                'description' => 'Просмотр интересного фильма или сериала',
                'metadata' => json_encode(['icon' => '🎬']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'listen_music',
                'name' => 'Прослушивание музыки',
                'description' => 'Наслаждение любимыми треками',
                'metadata' => json_encode(['icon' => '🎵']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'visit_place',
                'name' => 'Посещение места',
                'description' => 'Посещение интересного места поблизости',
                'metadata' => json_encode(['icon' => '📍']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('actions_service.actions')->insert($actions);
    }
}
