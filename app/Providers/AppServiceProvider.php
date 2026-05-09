<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Themes\AssetUsageAnalyzer;
use App\Services\Themes\ThemeCloner;
use App\Services\Themes\ThemeRegistry;
use App\Services\Themes\ThemeValidator;
use App\Services\Themes\ThemeWriter;
use App\Services\Themes\VariantScanner;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeRegistry::class, fn () => new ThemeRegistry(
            themesPath: resource_path('themes'),
        ));

        $this->app->singleton(ThemeWriter::class, fn ($app) => new ThemeWriter(
            registry: $app->make(ThemeRegistry::class),
            themesPath: resource_path('themes'),
        ));

        $this->app->singleton(ThemeCloner::class, fn ($app) => new ThemeCloner(
            registry: $app->make(ThemeRegistry::class),
            themesPath: resource_path('themes'),
        ));

        $this->app->singleton(VariantScanner::class, fn () => new VariantScanner(
            viewsPath: resource_path('views/sections'),
        ));

        $this->app->singleton(ThemeValidator::class, fn ($app) => new ThemeValidator(
            variants: $app->make(VariantScanner::class),
            themesPath: resource_path('themes'),
        ));

        $this->app->singleton(AssetUsageAnalyzer::class, fn () => new AssetUsageAnalyzer(
            themesPath: resource_path('themes'),
        ));
    }

    public function boot(): void
    {
        //
    }
}
