<?php

namespace Scrn\Journal;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Scrn\Journal\Events\ActivityPrepared;
use Scrn\Journal\Listeners\SaveActivity;

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
            __DIR__.'/../config/journal.php' => config_path('journal.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../migrations/' => database_path('migrations'),
        ], 'migrations');

        Event::subscribe(JournalEventSubscriber::class);

        Event::listen(ActivityPrepared::class, SaveActivity::class);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/journal.php', 'journal');
    }
}
