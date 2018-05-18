<?php

namespace Scrn\Journal\Tests\Models;

use Scrn\Journal\Models\Activity;
use Scrn\Journal\Tests\JournalTestCase;

class ActivityTest extends JournalTestCase
{
    /** @test */
    public function it_can_scope_activities_by_causer()
    {
        $users = factory(User::class, 2)->create();

        auth()->login($users[0]);

        factory(Role::class)->create();

        auth()->login($users[1]);

        factory(Role::class)->create();

        $this->assertCount(4, Activity::all());
        $this->assertCount(1, Activity::query()->causedBy($users[0])->get());
        $this->assertCount(1, Activity::query()->causedBy($users[1])->get());
    }

    /** @test */
    public function it_can_scope_activities_by_subject()
    {
        $users = factory(User::class, 2)->create();

        $this->assertCount(2, Activity::all());
        $this->assertCount(1, Activity::query()->forSubject($users[0])->get());
        $this->assertCount(1, Activity::query()->forSubject($users[1])->get());
    }
}
