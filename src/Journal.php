<?php

namespace Scrn\Journal;

use Illuminate\Database\Eloquent\Model;
use Scrn\Journal\Models\Activity;

class Journal
{
    /** @var string */
    protected $event;

    /** @var \Illuminate\Database\Eloquent\Model */
    protected $subject;

    /** @var \Illuminate\Database\Eloquent\Model */
    protected $causer;

    /** @var array */
    protected $old_data = [];

    /** @var array */
    protected $new_data = [];

    /**
     * Record the model the activity is applied on.
     *
     * @param \Illuminate\Database\Eloquent\Model $subject
     * @return $this
     */
    public function on(Model $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Record the model that caused the activity.
     *u
     * @param \Illuminate\Database\Eloquent\Model $causer
     * @return $this
     */
    public function by(Model $causer)
    {
        $this->causer = $causer;

        return $this;
    }

    /**
     * Set the event for the activity.
     *
     * @param string $event
     * @return $this
     */
    public function action(string $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Set the data for the activity.
     *
     * @param array $old_data
     * @param array $new_data
     * @return $this
     */
    public function data(array $old_data, array $new_data)
    {
        $this->old_data = $old_data;
        $this->new_data = $new_data;

        return $this;
    }

    /**
     * Save the log.
     *
     * @return \Scrn\Journal\Models\Activity
     */
    public function save()
    {
        /** @var Activity $activity */
        $activity = app(Activity::class)->newInstance();
        $activity->subject()->associate($this->subject);
        $activity->causer()->associate($this->causer);
        $activity->causer_snapshot = $this->causer ? $this->causer->toArray() : null;
        $activity->event = $this->event;
        $activity->old_data = $this->old_data;
        $activity->new_data = $this->new_data;
        $activity->save();

        return $activity;
    }
}