<?php

namespace Scrn\Journal\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Scrn\Journal\Concerns\LogsActivity;

class Article extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title',
        'content',
    ];

    protected $loggedAttributes = ['*'];
}
