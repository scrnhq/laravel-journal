<?php

use Scrn\Journal\Journal;

if (!function_exists('journal')) {
    function journal(): Journal
    {
        return app(Journal::class);
    }
}