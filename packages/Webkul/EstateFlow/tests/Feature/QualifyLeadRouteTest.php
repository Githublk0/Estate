<?php

namespace Tests\Feature\EstateFlow;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Webkul\EstateFlow\Models\Event;
use Webkul\User\Models\Role;
use Webkul\User\Models\User;

class QualifyLeadRouteTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        URL::forceRootUrl('http://localhost');
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->postJson('/api/estateflow/qualify-lead', ['lead_id' => 1])
            ->assertRedirect(route('admin.session.create'));

        $this->assertDatabaseCount('estateflow_events', 0);
    }

    public function test_authenticated_users_can_qualify_a_lead(): void
    {
        $role = Role::create([
            'name' => 'EstateFlow Test Administrator',
            'description' => 'EstateFlow route test role.',
            'permission_type' => 'all',
        ]);

        $user = User::create([
            'name' => 'EstateFlow Test User',
            'email' => 'estateflow-route-test@example.invalid',
            'password' => bcrypt('password'),
            'status' => 1,
            'role_id' => $role->id,
            'view_permission' => 'global',
        ]);

        $this->actingAs($user, 'user')
            ->postJson('/api/estateflow/qualify-lead', ['lead_id' => 1])
            ->assertSuccessful()
            ->assertJson([
                'score' => 0,
                'status' => 'New',
                'needs_human_review' => true,
            ]);

        $this->assertDatabaseHas('estateflow_events', [
            'lead_id' => 1,
            'user_id' => $user->id,
            'event_type' => 'lead.qualified',
        ]);
    }
}
