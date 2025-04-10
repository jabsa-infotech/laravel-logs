<?php

namespace jabsa\LaravelLogs;

use Illuminate\Support\ServiceProvider;

class LaravelLogsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-logs');
        
        $this->publishes([
            __DIR__.'/config/laravel-logs.php' => config_path('laravel-logs.php'),
        ], 'config');
        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/laravel-logs'),
        ], 'views');
        // publish the controllers
        $this->publishes([
            __DIR__.'/Http/Controllers' => app_path('Http/Controllers'),
        ], 'controllers');
    }

    public function register()
    {
        $this->app->bind('LaravelLogs', function($app) {
            return new LaravelLogs();
        });
    }
}