<?php

namespace App\Application\Providers;

use App\Application\Services\RecommendationService;
use App\Application\Services\WorldContextAnalyzer;
use App\Application\Services\WorldServiceClient;
use App\Domain\Repositories\ActionRepository;
use App\Domain\Repositories\MovieRepository;
use App\Domain\Repositories\RecommendationRepository;
use App\Domain\Repositories\SongRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Регистрация сервисов
     */
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Регистрация репозиториев
     */
    private function registerRepositories(): void
    {
        $this->app->singleton(ActionRepository::class);
        $this->app->singleton(SongRepository::class);
        $this->app->singleton(MovieRepository::class);
        $this->app->singleton(RecommendationRepository::class);
    }

    /**
     * Регистрация сервисов
     */
    private function registerServices(): void
    {
        $this->app->singleton(WorldContextAnalyzer::class);
        $this->app->singleton(WorldServiceClient::class);

        $this->app->singleton(RecommendationService::class, function ($app) {
            return new RecommendationService(
                $app->make(RecommendationRepository::class),
                $app->make(ActionRepository::class),
                $app->make(WorldContextAnalyzer::class)
            );
        });
    }

    /**
     * Загрузка сервисов
     */
    public function boot(): void
    {
        //
    }
}
