<?php

namespace Scrn\Journal\Tests\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Scrn\Journal\Concerns\HasActivity;
use Scrn\Journal\Concerns\LogsActivity;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasActivity;
    use LogsActivity;

    protected $fillable = [
        'email',
        'content',
    ];

    protected $loggedAttributes = ['*', 'roles.*'];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
