<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Создание схемы actions_service
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS actions_service');
    }

    /**
     * Удаление схемы actions_service
     */
    public function down(): void
    {
        DB::statement('DROP SCHEMA IF EXISTS actions_service CASCADE');
    }
};
