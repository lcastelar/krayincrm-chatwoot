<?php

namespace Webkul\Chatwoot\Providers;

use Illuminate\Support\ServiceProvider;

class ChatwootServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/routes.php');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'chatwoot');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'chatwoot');

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->publishes([
            __DIR__ . '/../Config/chatwoot.php' => config_path('chatwoot.php'),
        ], 'chatwoot-config');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/chatwoot.php', 'chatwoot'
        );
    }
}
