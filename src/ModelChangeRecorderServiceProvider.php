<?php

namespace Deen812\ModelChangeRecorder;

use Illuminate\Support\ServiceProvider;

class ModelChangeRecorderServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Регистрация зависимостей
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
