<?php

namespace Scrn\Journal\Tests\Feature\Concerns;

use Illuminate\Support\Facades\Request;
use Scrn\Journal\Models\Activity;
use Scrn\Journal\Tests\JournalTestCase;
use Scrn\Journal\Tests\Models\Article;
use Scrn\Journal\Tests\Models\Role;
use Scrn\Journal\Tests\Models\User;

class LogsActivityTest extends JournalTestCase
{
    protected $article;

    protected function setUp()
    {
        parent::setUp();

        $this->assertCount(0, Activity::all());
    }

    /** @test */
    public function it_logs_the_request_metadata()
    {
        factory(Article::class)->create();

        $activity = Activity::all()->last();
        $this->assertEquals('http://localhost', $activity->url);
        $this->assertEquals('127.0.0.1', $activity->ip_address);
        $this->assertEquals('Symfony', $activity->user_agent);
    }

    /** @test */
    public function it_logs_the_current_user()
    {
        $user = factory(User::class)->create();

        auth()->login($user);

        factory(Article::class)->create();

        $activity = Activity::all()->last();
        $this->assertTrue($activity->causer->is($user));
        $this->assertEquals($user->toArray(), $activity->causer_snapshot);
    }

    /** @test */
    public function it_logs_the_model_create_event()
    {
        $article = factory(Article::class)->create();

        $this->assertCount(1, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($article->id, $activity->subject_id);
        $this->assertEquals('created', $activity->event);
        $this->assertEquals([], $activity->old_data);
        $this->assertEquals(['title' => $article->title, 'content' => $article->content], $activity->new_data);
    }

    /** @test */
    public function it_logs_the_model_update_event()
    {
        $article = factory(Article::class)->create();

        $this->assertCount(1, Activity::all());

        $old_title = $article->title;
        $article->title = 'Other title';
        $article->save();

        $this->assertCount(2, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($article->id, $activity->subject_id);
        $this->assertEquals('updated', $activity->event);
        $this->assertEquals(['title' => $old_title], $activity->old_data);
        $this->assertEquals(['title' => $article->title], $activity->new_data);
    }

    /** @test */
    public function it_logs_the_model_delete_event()
    {
        $article = factory(Article::class)->create();

        $this->assertCount(1, Activity::all());

        $article->delete();

        $this->assertCount(2, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($article->id, $activity->subject_id);
        $this->assertEquals('deleted', $activity->event);
        $this->assertEquals(['title' => $article->title, 'content' => $article->content], $activity->old_data);
        $this->assertEquals([], $activity->new_data);
    }
}
