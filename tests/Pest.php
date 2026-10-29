<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Webkul\Attribute\Models\Attribute;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Source;
use Webkul\Lead\Models\Type;
use Webkul\User\Models\Role;
use Webkul\User\Models\User;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, the closure is bound to the PHPUnit\Framework\TestCase class.
| Of course, you may need to change that using the `uses()` function.
|
 */

uses(TestCase::class)->in('Feature');

/**
 * Keep test request paths aligned with their registered routes even when APP_URL contains a
 * subdirectory on the machine running the suite.
 */
uses()->beforeEach(function () {
    URL::forceRootUrl('http://localhost');
})->in('Feature');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/**
 * Get the default admin, creating one when the test database is not seeded.
 *
 * @return User
 */
function getDefaultAdmin()
{
    $admin = User::find(1) ?? User::query()->whereHas(
        'role',
        fn ($query) => $query->where('permission_type', 'all')
    )->first();

    if ($admin) {
        return $admin;
    }

    $role = Role::query()->firstOrCreate(
        ['name' => 'Administrator'],
        ['description' => 'Administrator role', 'permission_type' => 'all']
    );

    return User::create([
        'name' => 'Example',
        'email' => 'admin@example.com',
        'password' => bcrypt('admin123'),
        'status' => 1,
        'view_permission' => 'global',
        'role_id' => $role->id,
    ]);
}

/**
 * Sanctum authenticated admin.
 *
 * @return User
 */
function actingAsSanctumAuthenticatedAdmin()
{
    return Sanctum::actingAs(getDefaultAdmin(), ['*']);
}

/**
 * Get first name.
 *
 * @param string $fullName
 * @return string
 */
function getFirstName($fullName)
{
    return explode(' ', $fullName)[0];
}

/**
 * A default lead pipeline with the first stage, using the active driver schema.
 */
function securityTestPipeline(): Pipeline
{
    if (! Source::query()->count()) {
        Source::create(['name' => 'Web Form']);
    }

    if (! Type::query()->count()) {
        Type::create(['name' => 'New Business']);
    }

    $pipeline = Pipeline::query()->first();

    if (! $pipeline) {
        $pipeline = Pipeline::create([
            'name' => 'Security Test Pipeline',
            'is_default' => 1,
            'rotten_days' => 30,
        ]);
    }

    if (! $pipeline->stages()->count()) {
        $stage = [
            'probability' => 100,
            'sort_order' => 1,
        ];

        if (DB::connection()->getDriverName() === 'sqlite') {
            $stage['code'] = 'new';
            $stage['name'] = 'New';
        } else {
            $stage['lead_stage_id'] = DB::table('lead_stages')->value('id');
        }

        $pipeline->stages()->create($stage);
    }

    return $pipeline;
}

/**
 * An attribute by entity and code, created when it is absent.
 *
 * @param string $entityType
 * @param string $code
 * @return Attribute
 */
function webFormAttributeFor(string $entityType, string $code): Attribute
{
    $attribute = Attribute::query()
        ->where('entity_type', $entityType)
        ->where('code', $code)
        ->first();

    if ($attribute) {
        return $attribute;
    }

    $types = [
        'emails' => 'email',
        'contact_numbers' => 'phone',
    ];

    return Attribute::create([
        'code' => $code,
        'name' => ucfirst(str_replace('_', ' ', $code)),
        'type' => $types[$code] ?? 'text',
        'entity_type' => $entityType,
        'is_required' => 0,
        'is_unique' => 0,
        'quick_add' => 0,
        'sort_order' => 1,
    ]);
}
