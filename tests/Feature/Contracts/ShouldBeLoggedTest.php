<?php

namespace Scrn\Journal\Tests\Feature\Contracts;

use Scrn\Journal\Contracts\ShouldBeLogged;
use Scrn\Journal\Tests\JournalTestCase;

class Event implements ShouldBeLogged {
}

class ShouldBeLoggedTest extends JournalTestCase
{
    /** @test */
    public function it_should_log_an_event_that_implements_contract()
    {
        event(new Event());

        $this->assertDatabaseHas('activity_logs', ['event' => 'Event']);
    }
}
