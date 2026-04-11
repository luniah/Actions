<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создание таблицы песен
     */
    public function up(): void
    {
        Schema::create('actions_service.songs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('artist');
            $table->string('album')->nullable();
            $table->integer('duration')->nullable();
            $table->json('tags')->nullable();
            $table->string('external_id')->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('artist');
        });
    }

    /**
     * Удаление таблицы песен
     */
    public function down(): void
    {
        Schema::dropIfExists('actions_service.songs');
    }
};
