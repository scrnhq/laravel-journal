<?php

namespace Scrn\Journal\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Scrn\Journal\Models\Activity;

trait LogsActivity
{
    use DetectsChanges;
    use LogsRelatedActivity;
    use IsIgnorable;

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
                if (! $model->shouldLogEvent($event)) {
                    return;
                }

                $attributeGetter = $model->resolveAttributeGetter($event);

                list($old_data, $new_data) = method_exists($model, $attributeGetter) ? $model->$attributeGetter(...func_get_args()) : null;

                $activity = journal()->action($event)->on($model)->data($old_data, $new_data)->toActivity();

                $model->transformActivity($activity)->save();
            });
        }
    }

    /**
     * Get the attributes for the created event.
     *
     * @return array
     */
    public function getCreatedEventAttributes(): array
    {
        return [
            null,
            $this->getLoggedAttributeValues(),
        ];
    }

    /**
     * Get the attributes for the updated event.
     *
     * @return array
     */
    public function getUpdatedEventAttributes(): array
    {
        $new = [];
        $old = $this->getOldAttributes();

        foreach ($this->getLoggedAttributeValues() as $key => $value) {
            if (!$this->oldIsEquivalent($key, $value)) {
                $new[$key] = $value;
            }
        }

        $old = array_intersect_key($old, $new);

        return [
            $old,
            $new,
        ];
    }

    /**
     * Determine if the new and old value for a given key are equivalent.
     *
     * @return bool
     */
    protected function oldIsEquivalent($key, $new): bool
    {
        if (! array_key_exists($key, $this->oldAttributes)) {
            return false;
        }

        $original = $this->oldAttributes[$key];

        if ($new === $original) {
            return true;
        } elseif (is_null($new)) {
            return false;
        } elseif ($this->isDateAttribute($key)) {
            return $this->fromDateTime($new) ===
                $this->fromDateTime($original);
        } elseif ($this->hasCast($key)) {
            return $this->castAttribute($key, $new) ===
                $this->castAttribute($key, $original);
        }

        return is_numeric($new) && is_numeric($original)
            && strcmp((string) $new, (string) $original) === 0;
    }

    /**
     * Get the attributes for the deleted event.
     *
     * @return array
     */
    public function getDeletedEventAttributes(): array
    {
        return [
            $this->getLoggedAttributeValues(),
            null,
        ];
    }

    /**
     * Get the attributes for the restored event.
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
        $events = config()->get('journal.events', ['created', 'updated', 'deleted']);

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class))) {
            $events[] = 'restored';
        }

        if (in_array('Fico7489\Laravel\Pivot\Traits\PivotEventTrait', class_uses_recursive(static::class))) {
            $events = array_merge($events, ['pivotAttached', 'pivotDetached', 'pivotUpdated']);
        }

        $events = array_merge($events, $this->logged ?? []);

        return $events;
    }

    /**
     * Determine if the event should be logged.
     *
     * @param string $event
     * @return bool
     */
    public function shouldLogEvent(string $event): bool
    {
        if (array_has($this->getDirty(), 'deleted_at') && $this->getDirty()['deleted_at'] === null) {
            return false;
        }

        return ! $this->ignore($event);
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
     * Transform the activity data before saving.
     *
     * @param \Scrn\Journal\Models\Activity $activity
     * @return \Scrn\Journal\Models\Activity
     */
    public function transformActivity(Activity $activity)
    {
        return $activity;
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
