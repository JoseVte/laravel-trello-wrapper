<?php

namespace LaravelTrello;

use Illuminate\Support\ServiceProvider;

class TrelloServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/trello.php' => config_path('trello.php')
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/trello.php', 'trello');

        $this->app->singleton('trello', function ($app) {
            return new Wrapper($app['config']);
        });
    }
}
