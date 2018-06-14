<?php

namespace Scrn\Journal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Scrn\Journal\Models\Activity;
use Scrn\Journal\Resolvers\UserResolver;

class Journal
{
    /** @var string */
    protected $event;

    /** @var string */
    protected $description;

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
     * @return \Scrn\Journal\Journal
     */
    public function on(Model $subject): Journal
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Record the model that caused the activity.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $causer
     * @return \Scrn\Journal\Journal
     */
    public function by(Model $causer = null): Journal
    {
        $this->causer = $causer;

        return $this;
    }

    /**
     * Set the event for the activity.
     *
     * @param string $event
     * @return \Scrn\Journal\Journal
     */
    public function action(string $event): Journal
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Set the description for the activity.
     *
     * @param string $description
     * @return \Scrn\Journal\Journal
     */
    public function description(string $description): Journal
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the data for the activity.
     *
     * @param array $old_data
     * @param array $new_data
     * @return \Scrn\Journal\Journal
     */
    public function data(array $old_data = null, array $new_data = null): Journal
    {
        $this->old_data = $old_data;
        $this->new_data = $new_data;

        return $this;
    }

    /**
     * Create the activity.
     *
     * @return \Scrn\Journal\Models\Activity
     */
    public function toActivity(): Activity
    {
        /** @var Activity $activity */
        $activity = app(Activity::class)->newInstance();
        $activity->subject()->associate($this->subject);
        $activity->causer()->associate($this->causer ?? $this->resolveUser());
        $activity->causer_snapshot = $activity->causer ? $activity->causer->toArray() : null;
        $activity->event = $this->event;
        $activity->description = $this->description;
        $activity->old_data = $this->old_data;
        $activity->new_data = $this->new_data;

        $activity->url = $this->resolveUrl();
        $activity->ip_address = $this->resolveIp();
        $activity->user_agent = $this->resolveUserAgent();

        return $activity;
    }

    /**
     * Save the activity.
     *
     * @return \Scrn\Journal\Models\Activity
     */
    public function save(): Activity
    {
        $activity = $this->toActivity();

        $activity->save();

        return $activity;
    }

    /**
     * Resolve the User.
     *
     * @return mixed|null
     */
    protected function resolveUser()
    {
        $resolver = config('journal.resolvers.user', UserResolver::class);

        return call_user_func([$resolver, 'resolve']);
    }

    /**
     * Resolve the IP address.
     *
     * @return string
     */
    protected function resolveIp(): string
    {
        return Request::ip();
    }

    /**
     * Resolve the url.
     *
     * @return string
     */
    protected function resolveUrl(): string
    {
        return Request::fullUrl();
    }

    /**
     * Resolve the user agent.
     *
     * @return string
     */
    protected function resolveUserAgent(): string
    {
        return Request::userAgent();
    }
}
