<?php

use App\Http\Controllers\ActionsController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('actions')->group(function () {
        // Получить список всех доступных действий
        Route::get('/', [ActionsController::class, 'index']);

        // Получить рекомендацию действия
        Route::post('recommend', [ActionsController::class, 'recommend']);

        // Получить историю рекомендаций пользователя
        Route::get('history', [ActionsController::class, 'history']);
    });
});
