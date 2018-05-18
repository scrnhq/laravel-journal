<?php

namespace Scrn\Journal\Tests\Feature\Concerns;

use Scrn\Journal\Models\Activity;
use Scrn\Journal\Tests\JournalTestCase;
use Scrn\Journal\Tests\Models\Role;
use Scrn\Journal\Tests\Models\User;

class LogsRelatedActivityTest extends JournalTestCase
{
    /** @test */
    public function it_logs_the_many_to_many_related_attached_event()
    {
        $user = factory(User::class)->create();

        $role = factory(Role::class)->create();

        // User created, Role created
        $this->assertCount(2, Activity::all());

        $user->roles()->attach($role);

        // Role attached
        $this->assertCount(3, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($user->id, $activity->subject_id);
        $this->assertEquals('pivotAttached', $activity->event);
        $this->assertEquals(['roles' => []], $activity->old_data);
        $this->assertEquals(['roles' => [['id' => $role->id, 'name' => $role->name]]], $activity->new_data);
    }

    /** @test */
    public function it_logs_the_many_to_many_related_detached_event()
    {
        $user = factory(User::class)->create();
        $role = factory(Role::class)->create();
        $user->roles()->attach($role);

        // User created, Role created, Role attached
        $this->assertCount(3, Activity::all());

        $user->roles()->detach($role);

        // Role detached
        $this->assertCount(4, Activity::all());

        $activity = Activity::all()->last();
        $this->assertEquals($user->id, $activity->subject_id);
        $this->assertEquals('pivotDetached', $activity->event);
        $this->assertEquals(['roles' => [['id' => $role->id, 'name' => $role->name]]], $activity->old_data);
        $this->assertEquals(['roles' => []], $activity->new_data);
    }

    /** @test */
    public function it_logs_only_selected_pivot_relations()
    {
        $this->markTestIncomplete();

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
