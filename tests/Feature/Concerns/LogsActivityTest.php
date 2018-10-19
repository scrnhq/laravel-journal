<?php

namespace Scrn\Journal\Tests\Feature\Concerns;

use Illuminate\Support\Collection;
use Scrn\Journal\Models\Activity;
use Scrn\Journal\Tests\JournalTestCase;
use Scrn\Journal\Tests\Models\Article;
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
    public function it_has_many_activities()
    {
        $article = factory(Article::class)->create();

        $this->assertInstanceOf(Collection::class, $article->activity()->get());
        $this->assertTrue($article->activity()->first()->subject->is($article));
    }

    /** @test */
    public function it_logs_the_request_metadata()
    {
        factory(Article::class)->create();

        $activity = Activity::all()->last();
        $this->assertEquals('http://localhost', $activity->url);
        $this->assertEquals('127.0.0.1', $activity->ip_address);
        $this->assertNotNull($activity->user_agent);
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
        $user = factory(User::class)->create();

        // User created
        $this->assertCount(1, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($user->id, $activity->subject_id);
        $this->assertEquals('created', $activity->event);
        $this->assertEquals(null, $activity->old_data);
        $this->assertEquals(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'password' => $user->password], $activity->new_data);
    }

    /** @test */
    public function it_logs_the_model_update_event()
    {
        $user = factory(User::class)->create();

        // User created
        $this->assertCount(1, Activity::all());

        $oldName = $user->name;
        $user->name = 'John Doe';
        $user->save();

        // User updated
        $this->assertCount(2, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($user->id, $activity->subject_id);
        $this->assertEquals('updated', $activity->event);
        $this->assertEquals(['name' => $oldName], $activity->old_data);
        $this->assertEquals(['name' => $user->name], $activity->new_data);
    }

    /** @test */
    public function it_logs_the_model_delete_event()
    {
        $user = factory(User::class)->create();

        // User created
        $this->assertCount(1, Activity::all());

        $user->delete();

        // User deleted
        $this->assertCount(2, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($user->id, $activity->subject_id);
        $this->assertEquals('deleted', $activity->event);
        $this->assertEquals(['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'password' => $user->password], $activity->old_data);
        $this->assertEquals(null, $activity->new_data);
    }

    /** @test */
    public function it_logs_the_model_restored_event()
    {
        $article = factory(Article::class)->create();

        // User created, Article created
        $this->assertCount(2, Activity::all());

        $article->delete();

        // Article deleted
        $this->assertCount(3, Activity::all());

        $article->restore();

        // Article restored
        $this->assertCount(4, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($article->id, $activity->subject_id);
        $this->assertEquals('restored', $activity->event);
    }

    /** @test */
    public function it_logs_custom_model_events()
    {
        $article = factory(Article::class)->create();

        // User created, Article created
        $this->assertCount(2, Activity::all());

        $article->publish();

        // Article published, Article updated
        $this->assertCount(4, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($article->id, $activity->subject_id);
        $this->assertEquals('published', $activity->event);
        $this->assertEquals(null, $activity->old_data);
        $this->assertEquals(null, $activity->new_data);
    }

    /** @test */
    public function it_ignores_model_events()
    {
        $article = factory(Article::class)->create();

        // User created, Article created
        $this->assertCount(2, Activity::all());

        $article->perish();

        // Article perished, and Article also updated but was ignored
        $this->assertCount(3, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($article->id, $activity->subject_id);
        $this->assertEquals('perished', $activity->event);
        $this->assertEquals(null, $activity->old_data);
        $this->assertEquals(null, $activity->new_data);
    }
}
