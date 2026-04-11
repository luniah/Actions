<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создание таблицы фильмов
     */
    public function up(): void
    {
        Schema::create('actions_service.movies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('genres')->nullable();
            $table->integer('release_year')->nullable();
            $table->integer('duration')->nullable();
            $table->string('director')->nullable();
            $table->string('poster_url')->nullable();
            $table->string('external_id')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('release_year');
        });
    }

    /**
     * Удаление таблицы фильмов
     */
    public function down(): void
    {
        Schema::dropIfExists('actions_service.movies');
    }
};
