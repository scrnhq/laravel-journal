<?php

namespace Scrn\Journal\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Scrn\Journal\Models\Activity;

trait HasActivity
{
    /**
     * The activity associated with this model.
     *
     * @return mixed
     */
    public function actions(): MorphMany
    {
        return $this->morphMany(Activity::class, 'causer');
    }
}