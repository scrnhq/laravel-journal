<?php

namespace Scrn\Journal\Tests\Feature\Concerns;

use Scrn\Journal\Tests\JournalTestCase;
use Scrn\Journal\Tests\Models\Article;
use Scrn\Journal\Tests\Models\User;

class HasActivityTest extends JournalTestCase
{
    /** @test */
    public function it_can_retrieve_the_actions_caused_by_the_model()
    {
        $user = factory(User::class)->create();

        $this->assertEmpty($user->actions()->get());

        auth()->login($user);

        factory(Article::class)->create(['user_id' => $user]);

        $this->assertCount(1, $user->actions()->get());
    }
}
