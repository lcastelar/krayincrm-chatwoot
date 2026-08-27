<?php

namespace Webkul\Reports\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ReportsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::middleware(['web', 'admin_locale', 'user'])
            ->prefix(config('app.admin_path'))
            ->group(__DIR__ . '/../Routes/routes.php');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'reports');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'reports');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/menu.php', 'menu.admin'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../Config/acl.php', 'acl'
        );
    }
}
