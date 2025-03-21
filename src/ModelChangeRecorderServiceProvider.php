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
        $this->publishes(
            [
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ],
            'migrations'
        );
    }
}
