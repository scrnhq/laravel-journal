<?php

namespace Scrn\Journal;

use Illuminate\Support\ServiceProvider;

class JournalServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/journal.php' => config_path('journal.php')
        ], 'config');

        $this->loadMigrationsFrom(__DIR__ . '/../migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/journal.php', 'journal');
    }
}
