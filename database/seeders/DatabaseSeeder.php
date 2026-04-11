<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Заполнение базы данных начальными данными
     */
    public function run(): void
    {
        $this->call([
            ActionSeeder::class,
            SongSeeder::class,
            MovieSeeder::class,
        ]);
    }
}
