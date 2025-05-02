<?php

namespace SeedBuilder;

use SeedBuilder\Console\ExportSeedCommand;
use SeedBuilder\Console\GenerateSeedCommand;

class SeedBuilderServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExportSeedCommand::class,
                GenerateSeedCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/seedbuilder.php' => config_path('seedbuilder.php'),
            ], 'seedbuilder-config');
        }
    }
}
