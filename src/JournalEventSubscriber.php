<?php

namespace Scrn\Journal;

use Illuminate\Events\Dispatcher;
use Scrn\Journal\Contracts\ShouldBeLogged;

class JournalEventSubscriber
{
    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen('*', static::class . '@handle');
    }

    /**
     * Handle the event.
     *
     * @param $event
     * @param $payload
     */
    protected function handle($event, $payload)
    {
        if (!$this->shouldBeLogged($event)) {
            return;
        }

        $this->store($payload[0]);
    }

    /**
     * Store the event.
     *
     * @param ShouldBeLogged $event
     */
    protected function store(ShouldBeLogged $event)
    {
        $journal = journal()->by(auth()->user());

        $event->store($journal);
    }

    /**
     * Determine if the event should be logged.
     *
     * @param $event
     * @return bool
     */
    protected function shouldBeLogged($event)
    {
        return is_subclass_of($event, ShouldBeLogged::class);
    }
}
