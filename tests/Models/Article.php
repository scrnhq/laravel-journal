<?php

namespace Scrn\Journal\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Scrn\Journal\Concerns\LogsActivity;

class Article extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'content',
    ];

    protected $observables = ['published'];

    protected $logged = ['published'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publish()
    {
        $this->published_at = now();
        $this->save();
        $this->fireModelEvent('published');
        return $this;
    }
}
