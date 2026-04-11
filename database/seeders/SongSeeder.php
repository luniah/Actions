<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SongSeeder extends Seeder
{
    /**
     * Заполнение таблицы песен начальными данными
     */
    public function run(): void
    {
        $songs = [
            [
                'name' => 'Jingle Bells',
                'artist' => 'Frank Sinatra',
                'album' => 'Christmas Songs',
                'duration' => 156,
                'tags' => json_encode(['christmas', 'happy', 'classic']),
                'external_id' => 'lastfm_1',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Let It Snow',
                'artist' => 'Dean Martin',
                'album' => 'Christmas With Dean',
                'duration' => 192,
                'tags' => json_encode(['christmas', 'chill', 'classic']),
                'external_id' => 'lastfm_2',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Blinding Lights',
                'artist' => 'The Weeknd',
                'album' => 'After Hours',
                'duration' => 200,
                'tags' => json_encode(['pop', 'energetic', 'synthwave']),
                'external_id' => 'lastfm_3',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Shape of You',
                'artist' => 'Ed Sheeran',
                'album' => 'Divide',
                'duration' => 233,
                'tags' => json_encode(['pop', 'happy', 'dance']),
                'external_id' => 'lastfm_4',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Someone Like You',
                'artist' => 'Adele',
                'album' => '21',
                'duration' => 285,
                'tags' => json_encode(['sad', 'ballad', 'soul']),
                'external_id' => 'lastfm_5',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Happy',
                'artist' => 'Pharrell Williams',
                'album' => 'Girl',
                'duration' => 233,
                'tags' => json_encode(['happy', 'pop', 'funk']),
                'external_id' => 'lastfm_6',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bohemian Rhapsody',
                'artist' => 'Queen',
                'album' => 'A Night at the Opera',
                'duration' => 354,
                'tags' => json_encode(['rock', 'classic', 'epic']),
                'external_id' => 'lastfm_7',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Billie Jean',
                'artist' => 'Michael Jackson',
                'album' => 'Thriller',
                'duration' => 294,
                'tags' => json_encode(['pop', 'dance', 'classic']),
                'external_id' => 'lastfm_8',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Smells Like Teen Spirit',
                'artist' => 'Nirvana',
                'album' => 'Nevermind',
                'duration' => 301,
                'tags' => json_encode(['rock', 'grunge', 'energetic']),
                'external_id' => 'lastfm_9',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rolling in the Deep',
                'artist' => 'Adele',
                'album' => '21',
                'duration' => 228,
                'tags' => json_encode(['soul', 'pop', 'powerful']),
                'external_id' => 'lastfm_10',
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($songs as $song) {
            DB::table('actions_service.songs')->updateOrInsert(
                ['external_id' => $song['external_id']],
                $song
            );
        }
    }
}
