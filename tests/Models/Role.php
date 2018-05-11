<?php

namespace Scrn\Journal\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Scrn\Journal\Concerns\LogsActivity;

class Role extends Model
{
    use LogsActivity;

    protected $fillable = [
        'email',
        'content',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
