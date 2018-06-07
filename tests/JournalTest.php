<?php

namespace Scrn\Journal\Tests;

use Scrn\Journal\Tests\Models\User;
use Scrn\Journal\Tests\Resolvers\CustomUserResolver;

class JournalTest extends JournalTestCase
{
    /** @test */
    public function it_can_override_the_user_resolver()
    {
        config()->set('journal.resolvers.user', CustomUserResolver::class);

        auth()->login(factory(User::class)->create());

        $activity = journal()->action('event')->save();

        $this->assertNull($activity->causer);
    }
}
