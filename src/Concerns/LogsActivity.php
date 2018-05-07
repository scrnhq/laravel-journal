<?php

namespace App\Logs\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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

                $data = $model->$attributeGetter(...func_get_args());

                journal()->action($event)->on($model)->data($data)->save();
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
        return [
            [],
            $this->attributes,
        ];
    }

    /**
     * Get the attributes for the updated event.
     *
     * @return array
     */
    public function getUpdatedEventAttributes(): array
    {
        return [
            $this->original,
            $this->attributes,
        ];
    }

    /**
     * Get the attributes for the deleted event.
     *
     * @return array
     */
    public function getDeletedEventAttributes(): array
    {
        return [
            $this->attributes,
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
        return $this->logged ?? config('activitylog.events', [
                'created',
                'updated',
                'deleted',
                'restored',
            ]);
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