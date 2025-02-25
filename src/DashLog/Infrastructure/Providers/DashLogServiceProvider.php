<?php

namespace AledDev\DashLog\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use AledDev\DashLog\Domain\Contracts\RequestLogRepositoryInterface;
use AledDev\DashLog\Infrastructure\Persistence\Eloquent\Repositories\EloquentRequestLogRepository;
use AledDev\DashLog\Infrastructure\Persistence\Elasticsearch\ElasticsearchRequestLogRepository;
use AledDev\DashLog\Application\Presenters\RequestLogPresenterInterface;
use AledDev\DashLog\Application\Presenters\DefaultRequestLogPresenter;
use AledDev\DashLog\Application\Presenters\JsonRequestLogPresenter;
use AledDev\DashLog\Infrastructure\Http\Middleware\RequestMonitorMiddleware;
use Illuminate\Contracts\Http\Kernel;
use OpenAI\Client;
use Illuminate\Support\Facades\Route;

class DashLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            return \OpenAI::client(config('dashlog.ai_analysis.api_key'));
        });

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

        $this->publishes([
            __DIR__ . '/../Persistence/Migrations' => database_path('migrations')
        ], 'dashlog-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \AledDev\DashLog\Infrastructure\Console\Commands\SetupElasticsearchCommand::class
            ]);
        }
        $this->loadMigrationsFrom(__DIR__ . '/../Persistence/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'dashlog');

        $kernel = $this->app[Kernel::class];
        $kernel->pushMiddleware(RequestMonitorMiddleware::class);

        Route::aliasMiddleware('request.monitor', RequestMonitorMiddleware::class);
    }
} 