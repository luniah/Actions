<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создание таблицы рекомендаций
     */
    public function up(): void
    {
        Schema::create('actions_service.recommendations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action_type');
            $table->unsignedBigInteger('action_id')->nullable();
            $table->json('context')->nullable();
            $table->json('suggestions')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id');
            $table->index('action_type');
            $table->index('created_at');
        });
    }

    /**
     * Удаление таблицы рекомендаций
     */
    public function down(): void
    {
        Schema::dropIfExists('actions_service.recommendations');
    }
};
