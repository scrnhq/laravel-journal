<?php

namespace Scrn\Journal\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $event
 * @property string $old_data
 * @property string $new_data
 * @property string $subject_id
 * @property string $subject_type
 * @property-read Model $subject
 * @property string $causer_id
 * @property string $causer_type
 * @property string $causer_snapshot
 * @property-read Model $causer
 * @property string $url
 * @property string $ip_address
 * @property string $user_agent
 */
class Activity extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activity_logs';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'causer_snapshot' => 'array',
    ];

    /**
     * The model associated with the activity.
     *
     * @return MorphTo
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The model that caused the activity.
     *
     * @return MorphTo
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @param Builder $query
     * @param Model $subject
     * @return Builder
     */
    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }

    /**
     * @param Builder $query
     * @param Model $causer
     * @return Builder
     */
    public function scopeCausedBy(Builder $query, Model $causer): Builder
    {
        return $query->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }
}
