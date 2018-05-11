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
            __DIR__ . '/../config/journal.php' => config_path('journal.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../migrations/' => database_path('migrations'),
        ], 'migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/journal.php', 'journal');
    }
}
