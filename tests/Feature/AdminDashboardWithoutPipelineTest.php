<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\User\Models\Role;
use Webkul\User\Models\User;

uses(DatabaseTransactions::class);

function bindDashboardWithoutPipeline(): void
{
    $pipelineRepository = Mockery::mock(PipelineRepository::class)->makePartial();

    $pipelineRepository->shouldReceive('getDefaultPipeline')->andReturn(null);
    $pipelineRepository->shouldReceive('all')->andReturn(collect());

    app()->instance(PipelineRepository::class, $pipelineRepository);
}

function createDashboardWithoutPipelineUser(): User
{
    $role = Role::create([
        'name' => 'Dashboard without pipeline '.bin2hex(random_bytes(5)),
        'description' => 'Dashboard regression test role.',
        'permission_type' => 'all',
    ]);

    return User::create([
        'name' => 'Dashboard Regression User',
        'email' => bin2hex(random_bytes(5)).'@example.invalid',
        'password' => bcrypt('test-password'),
        'status' => 1,
        'view_permission' => 'global',
        'role_id' => $role->id,
    ]);
}

it('renders the authenticated dashboard when no default pipeline exists', function () {
    bindDashboardWithoutPipeline();

    $this->actingAs(createDashboardWithoutPipelineUser(), 'user')
        ->get(route('admin.dashboard.index'))
        ->assertSuccessful();
});

it('serves every dashboard widget when no default pipeline exists', function () {
    bindDashboardWithoutPipeline();

    $user = createDashboardWithoutPipelineUser();

    foreach ([
        'over-all',
        'revenue-stats',
        'total-leads',
        'revenue-by-sources',
        'revenue-by-types',
        'top-selling-products',
        'top-persons',
        'open-leads-by-states',
    ] as $type) {
        $this->actingAs($user, 'user')
            ->get(route('admin.dashboard.stats', ['type' => $type]))
            ->assertSuccessful();
    }
});
