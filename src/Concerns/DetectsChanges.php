<?php

namespace Scrn\Journal\Concerns;

use Illuminate\Database\Eloquent\Model;

trait DetectsChanges
{
    /** @var array */
    protected $oldAttributes;

    /**
     * Boot the detects changes trait for a model.
     *
     * @return void
     */
    protected static function bootDetectsChanges()
    {
        static::saving(function (Model $model) {
            $oldModel = $model->replicate()->setRawAttributes($model->getOriginal());

            $model->oldAttributes = $oldModel->getLoggedAttributeValues();
        });
    }

    /**
     * Get the old attributes of the model.
     *
     * @return array
     */
    public function getOldAttributes(): array
    {
        return $this->oldAttributes;
    }

    /**
     * Get the values of the attributes that should be logged.
     *
     * @return array
     */
    public function getLoggedAttributeValues(): array
    {
        $attributes = [];

        foreach ($this->getLoggedAttributes() as $attribute) {
            $attributes[$attribute] = $this->getAttribute($attribute);
        }

        return $attributes;
    }

    /**
     * Get the attributes that should be logged.
     *
     * @return array
     */
    public function getLoggedAttributes(): array
    {
        $attributes = $this->loggedAttributes ?? array_keys($this->attributes);

        if (! $this->shouldLogTimestamps()) {
            $attributes = array_diff($attributes, [static::CREATED_AT, static::UPDATED_AT]);
        }

        return array_diff($attributes, $this->getIgnoredAttributes());
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
        return $this->logTimestamps ?? config()->get('journal.timestamps', false);
    }
}
