<?php

namespace Scrn\Journal\Contracts;

use Scrn\Journal\Journal;

interface ShouldBeLogged
{
    public function store(Journal $journal);
}
