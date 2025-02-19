<?php

namespace DashLog\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use DashLog\Infrastructure\Persistence\Eloquent\Repositories\EloquentRequestLogRepository;
use DashLog\Infrastructure\Persistence\Elasticsearch\ElasticsearchRequestLogRepository;
use DashLog\Application\Presenters\RequestLogPresenterInterface;
use DashLog\Application\Presenters\DefaultRequestLogPresenter;
use DashLog\Application\Presenters\JsonRequestLogPresenter;

class DashLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RequestLogRepositoryInterface::class, function ($app) {
            return config('dashlog.storage.driver') === 'elasticsearch'
                ? new ElasticsearchRequestLogRepository()
                : new EloquentRequestLogRepository();
        });

        $this->app->bind(RequestLogPresenterInterface::class, function ($app) {
            $format = config('dashlog.presentation.format', 'default');
            
            return match ($format) {
                'json' => new JsonRequestLogPresenter(),
                default => new DefaultRequestLogPresenter(),
            };
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/dashlog.php',
            'dashlog'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/dashlog.php' => config_path('dashlog.php'),
        ], 'dashlog-config');
        if ($this->app->runningInConsole()) {
            $this->commands([
                \DashLog\Infrastructure\Console\Commands\SetupElasticsearchCommand::class
            ]);
        }
        $this->loadMigrationsFrom(__DIR__ . '/../Persistence/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'dashlog');
    }
} 