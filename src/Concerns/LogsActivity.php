<?php

namespace Scrn\Journal\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Scrn\Journal\Models\Activity;

trait LogsActivity
{
    use LogsRelatedActivity;

    /**
     * @var array
     */
    protected $originalRelations = [];

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

                list($old_data, $new_data) = method_exists($model, $attributeGetter)
                    ? $model->$attributeGetter(...func_get_args())
                    : [[], []];

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
            if ($this->shouldAttributeBeLogged($attribute)) {
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
            if ($this->shouldAttributeBeLogged($attribute)) {
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
            if ($this->shouldAttributeBeLogged($attribute)) {
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
        return array_reverse($this->getDeletedEventAttributes());
    }

    /**
     * Get the events that should be logged.
     *
     * @return array
     */
    public function getLoggedEvents(): array
    {
        if (isset($this->logged)) {
            return $this->logged;
        }

        $events = Config::get('journal.events', ['created', 'updated', 'deleted']);

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            $events[] = 'restored';
        }

        if (in_array('Fico7489\Laravel\Pivot\Traits\PivotEventTrait', class_uses_recursive(static::class))) {
            $events = array_merge($events, ['pivotAttached', 'pivotDetached', 'pivotUpdated']);
        }

        return $events;
    }

    /**
     * Determine if the attribute should be logged.
     *
     * @param string $attribute
     * @return bool
     */
    public function shouldAttributeBeLogged(string $attribute): bool
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
        $attributes = $this->loggedAttributes ?? array_keys($this->attributes);

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
     * Get the models original relation values.
     *
     * @param string $key
     * @param null $default
     * @return mixed|array
     */
    public function getOriginalRelation(string $key, $default = null)
    {
        return Arr::get($this->originalRelations, $key, $default);
    }

    /**
     * Sync a single original relation with its current value.
     *
     * @param string $relation
     * @return $this
     */
    public function syncOriginalRelation(string $relation)
    {
        $this->originalRelations[$relation] = $this->getRelationValue($relation);

        return $this;
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
