<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Создание таблицы действий
     */
    public function up(): void
    {
        Schema::create('actions_service.actions', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Удаление таблицы действий
     */
    public function down(): void
    {
        Schema::dropIfExists('actions_service.actions');
    }
};
