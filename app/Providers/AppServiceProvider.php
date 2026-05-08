<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Themes\ThemeRegistry;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeRegistry::class, fn () => new ThemeRegistry(
            themesPath: resource_path('themes'),
        ));
    }

    public function boot(): void
    {
        //
    }
}
