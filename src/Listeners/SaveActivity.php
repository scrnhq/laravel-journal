<?php

namespace Scrn\Journal\Listeners;

use Scrn\Journal\Events\ActivityPrepared;

class SaveActivity
{
    public function handle(ActivityPrepared $event)
    {
        $event->getActivity()->save();
    }
}
