<?php

namespace Scrn\Journal\Tests\Feature\Concerns;

use Scrn\Journal\Models\Activity;
use Scrn\Journal\Tests\JournalTestCase;
use Scrn\Journal\Tests\Models\Article;
use Scrn\Journal\Tests\Models\Role;
use Scrn\Journal\Tests\Models\User;

class LogsRelatedActivityTest extends JournalTestCase
{
    /** @test */
    public function it_logs_the_belongs_to_related_attributes()
    {
        $user = factory(User::class)->create();
        $article = factory(Article::class)->create(['user_id' => $user->id]);

        // User created, Article created
        $this->assertCount(2, Activity::all());

        $newUser = factory(User::class)->create();

        // User created
        $this->assertCount(3, Activity::all());

        $article->user()->associate($newUser);
        $article->save();

        // Article updated
        $this->assertCount(4, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($article->id, $activity->subject_id);
        $this->assertEquals('updated', $activity->event);
        $this->assertEquals(['user_id' => $user->id, 'user' => ['id' => $user->id, 'name' => $user->name]], $activity->old_data);
        $this->assertEquals(['user_id' => $newUser->id, 'user' => ['id' => $newUser->id, 'name' => $newUser->name]], $activity->new_data);
    }

    /** @test */
    public function it_logs_the_many_to_many_related_attached_event()
    {
        $user = factory(User::class)->create();

        $this->assertCount(1, Activity::all());

        $role = factory(Role::class)->create();

        $this->assertCount(2, Activity::all());

        $user->roles()->attach($role);

        $this->assertCount(3, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($user->id, $activity->subject_id);
        $this->assertEquals('pivotAttached', $activity->event);
        $this->assertEquals(['roles' => []], $activity->old_data);
        $this->assertEquals(['roles' => [['id' => $role->id, 'name' => $role->name]]], $activity->new_data);
    }

    /** @test */
    public function it_logs_only_selected_pivot_relations()
    {
        $user = factory(User::class)->create();

        $this->assertCount(1, Activity::all());

        $role = factory(Role::class)->create();

        $this->assertCount(2, Activity::all());

        $user->roles()->attach($role);

        $this->assertCount(3, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($user->id, $activity->subject_id);
        $this->assertEquals('pivotAttached', $activity->event);
        $this->assertEquals(['roles' => []], $activity->old_data);
        $this->assertEquals(['roles' => [['id' => $role->id, 'name' => $role->name]]], $activity->new_data);
    }
}
