<?php

namespace SeedBuilder;

use Illuminate\Support\ServiceProvider;
use SeedBuilder\Console\ExportSeedCommand;
use SeedBuilder\Console\GenerateSeedCommand;

class SeedBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/seedbuilder.php', 'seedbuilder');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExportSeedCommand::class,
                GenerateSeedCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/seedbuilder.php' => config_path('seedbuilder.php'),
            ], 'seedbuilder-config');
        }
    }
}
