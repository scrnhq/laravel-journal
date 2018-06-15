<?php

namespace Scrn\Journal\Events;

use Scrn\Journal\Models\Activity;

class ActivityPrepared
{
    protected $activity;

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    public function getActivity(): Activity
    {
        return $this->activity;
    }
}
