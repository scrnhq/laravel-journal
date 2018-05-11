<?php

namespace Scrn\Journal\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Config;
use Scrn\Journal\Models\Activity;

trait LogsActivity
{
    /**
     * Boot the logs activity trait for a model.
     *
     * @return void
     */
    protected static function bootLogsActivity()
    {
        $instance = new static;

        foreach ($instance->getLoggedEvents() as $event) {
            static::registerModelEvent($event, function (Model $model) use ($event) {
                $attributeGetter = $model->resolveAttributeGetter($event);

                if (!method_exists($model, $attributeGetter)) {
                    throw new \Exception($attributeGetter . ' not found on ' . get_class($model) . '.');
                }

                list($old_data, $new_data) = $model->$attributeGetter(...func_get_args());

                journal()->action($event)
                    ->on($model)
                    ->by(auth()->user())
                    ->data($old_data, $new_data)
                    ->save();
            });
        }
    }

    /**
     * Get the attributes for a created event.
     *
     * @return array
     */
    public function getCreatedEventAttributes(): array
    {
        $attributes = [];

        foreach ($this->attributes as $attribute => $value) {
            if ($this->shouldBeLogged($attribute)) {
                $attributes[$attribute] = $value;
            }
        }

        return [
            [],
            $attributes,
        ];
    }

    /**
     * Get the attributes for the updated event.
     *
     * @return array
     */
    public function getUpdatedEventAttributes(): array
    {
        $old = [];
        $new = [];

        foreach ($this->getDirty() as $attribute => $value) {
            if ($this->shouldBeLogged($attribute)) {
                $old[$attribute] = $this->getOriginal($attribute);
                $new[$attribute] = $this->getAttribute($attribute);
            }
        }

        return [
            $old,
            $new,
        ];
    }

    /**
     * Get the attributes for the deleted event.
     *
     * @return array
     */
    public function getDeletedEventAttributes(): array
    {
        $attributes = [];

        foreach ($this->attributes as $attribute => $value) {
            if ($this->shouldBeLogged($attribute)) {
                $attributes[$attribute] = $value;
            }
        }

        return [
            $attributes,
            [],
        ];
    }

    /**
     * Get the attributes for the deleted event.
     *
     * @return array
     */
    public function getRestoredEventAttributes(): array
    {
        return [
            [],
            $this->attributes,
        ];
    }

    /**
     * Get the events that should be logged.
     *
     * @return array
     */
    public function getLoggedEvents(): array
    {
        return $this->logged ?? config('journal.events', [
                'created',
                'updated',
                'deleted',
                'restored',
            ]);
    }

    /**
     * Determine if the attribute should be logged.
     *
     * @param string $attribute
     * @return bool
     */
    public function shouldBeLogged(string $attribute): bool
    {
        return in_array($attribute, $this->getLoggedAttributes()) && !in_array($attribute, $this->getIgnoredAttributes());
    }

    /**
     * Get the attributes that should be logged.
     *
     * @return array
     */
    public function getLoggedAttributes(): array
    {
        $attributes = [];

        if (isset($this->loggedAttributes)) {
            if (in_array('*', $this->loggedAttributes)) {
                $attributes = array_merge(array_keys($this->attributes), array_diff($this->loggedAttributes, ['*']));
            } else {
                $attributes = $this->loggedAttributes;
            }
        }

        if (!$this->shouldLogTimestamps()) {
            $attributes = array_diff($attributes, [static::CREATED_AT, static::UPDATED_AT]);
        }

        return $attributes;
    }

    /**
     * Get the attributes that should never be logged.
     *
     * @return array
     */
    public function getIgnoredAttributes(): array
    {
        return $this->ignoredAttributes ?? [];
    }

    /**
     * Determine if timestamps should be logged.
     *
     * @return bool
     */
    public function shouldLogTimestamps(): bool
    {
        return $this->logTimestamps ?? Config::get('journal.timestamps', false);
    }

    /**
     * Determine if the event should be logged.
     *
     * @param string $event
     * @return bool
     */
    public function shouldLogEvent(string $event): bool
    {
        return true;
    }

    /**
     * Resolve the attribute getter for the given event.
     *
     * @param string $event
     * @return string
     */
    public function resolveAttributeGetter(string $event): string
    {
        return sprintf('get%sEventAttributes', studly_case($event));
    }

    /**
     * The activities performed on this model.
     *
     * @return mixed
     */
    public function activity(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}
