<?php

namespace PhpNl\LaravelPayloadEditor;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use PhpNl\LaravelPayloadEditor\Contracts\FailedJobRepository;
use PhpNl\LaravelPayloadEditor\Livewire\LaravelPayloadEditorDashboard;
use PhpNl\LaravelPayloadEditor\Repositories\DatabaseFailedJobRepository;
use PhpNl\LaravelPayloadEditor\Repositories\HorizonFailedJobRepository;

class LaravelPayloadEditorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-payload-editor.php', 'laravel-payload-editor');

        $this->app->bind(FailedJobRepository::class, function ($app) {
            return match ($app['config']->get('laravel-payload-editor.driver')) {
                'horizon' => new HorizonFailedJobRepository,
                default => new DatabaseFailedJobRepository,
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-payload-editor.php' => config_path('laravel-payload-editor.php'),
            ], 'laravel-payload-editor-config');

            // Optionally publish views later
            // $this->publishes([
            //     __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-payload-editor'),
            // ], 'laravel-payload-editor-views');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-payload-editor');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        Gate::define('viewLaravelPayloadEditor', function ($user = null) {
            return app()->environment('local');
        });

        if (class_exists(Livewire::class)) {
            Livewire::component('laravel-payload-editor-dashboard', LaravelPayloadEditorDashboard::class);
        }
    }
}
