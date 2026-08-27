<?php

namespace Webkul\Chatwoot\Providers;

use Illuminate\Support\ServiceProvider;
use Webkul\Chatwoot\Observers\PersonObserver;
use Webkul\Chatwoot\Observers\TagObserver;
use Webkul\Chatwoot\Services\ChatwootApiService;
use Webkul\Contact\Models\Person;
use Webkul\Tag\Models\Tag;

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

        // Register Observers for Real-Time Krayin -> Chatwoot Synchronization
        Person::observe(PersonObserver::class);
        Tag::observe(TagObserver::class);

        // Register Console Commands & Automated Scheduled Sync
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Webkul\Chatwoot\Console\Commands\SyncTagsCommand::class,
            ]);
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            $schedule->command('chatwoot:sync-tags')->everyFifteenMinutes();
        });
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/chatwoot.php', 'chatwoot'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/menu.php', 'menu.admin'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/acl.php', 'acl'
        );

        $this->app->singleton(ChatwootApiService::class, function () {
            return new ChatwootApiService();
        });
    }
}
