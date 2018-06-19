<?php

namespace Scrn\Journal\Concerns;

use Illuminate\Database\Eloquent\Model;

trait LogsRelatedActivity
{
    /**
     * Boot the logs activity trait for a model.
     *
     * @return void
     */
    public static function bootLogsRelatedActivity()
    {
        if (!in_array('Fico7489\Laravel\Pivot\Traits\PivotEventTrait', class_uses_recursive(static::class))) {
            return;
        }

        foreach (['pivotAttaching', 'pivotDetaching', 'pivotUpdating'] as $event) {
            static::registerModelEvent($event, function (Model $model, $relation) use ($event) {
                $model->syncOriginalRelation($relation);
            });
        }
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
        return array_get($this->originalRelations, $key, $default);
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
     * Get the attributes for the pivot attached event.
     *
     * @param Model $model
     * @param $relation
     * @return array
     */
    public function getPivotAttachedEventAttributes(Model $model, $relation, $ids): array
    {
        $old = [
            $relation => $this->getOriginalRelation($relation)->pluck('pivot'),
        ];
        $new = [
            $relation => $this->getRelationshipFromMethod($relation)->pluck('pivot'),
        ];

        return [
            $old,
            $new,
        ];
    }

    /**
     * Get the attributes for the pivot detached event.
     *
     * @param Model $model
     * @param $relation
     * @return array
     */
    public function getPivotDetachedEventAttributes(Model $model, $relation, $ids): array
    {
        $old = [
            $relation => $this->getOriginalRelation($relation)->pluck('pivot'),
        ];
        $new = [
            $relation => $this->getRelationshipFromMethod($relation)->pluck('pivot'),
        ];

        return [
            $old,
            $new,
        ];
    }

    /**
     * Get the attributes for the pivot updated event.
     *
     * @param Model $model
     * @param $relation
     * @return array
     */
    public function getPivotUpdatedEventAttributes(Model $model, $relation, $ids): array
    {
        $old = [
            $relation => $this->getOriginalRelation($relation)->pluck('pivot'),
        ];
        $new = [
            $relation => $this->getRelationshipFromMethod($relation)->pluck('pivot'),
        ];

        return [
            $old,
            $new,
        ];
    }
}
