<?php

namespace Tests\Feature\EstateFlow;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Webkul\EstateFlow\Models\Property;
use Webkul\User\Models\Role;
use Webkul\User\Models\User;

class PropertyTenantIsolationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_an_agency_cannot_read_another_agencys_properties(): void
    {
        [$agency, $otherAgency] = $this->createAgencies();
        $propertyIds = $this->createProperties($agency, $otherAgency);

        $this->actingAs($agency, 'user');

        $this->assertNotNull(Property::query()->find($propertyIds['agency']));
        $this->assertNull(Property::query()->find($propertyIds['other']));
    }

    public function test_an_agency_cannot_update_another_agencys_properties(): void
    {
        [$agency, $otherAgency] = $this->createAgencies();
        $propertyIds = $this->createProperties($agency, $otherAgency);

        $this->actingAs($agency, 'user');

        $this->assertSame(
            0,
            Property::query()->whereKey($propertyIds['other'])->update(['name' => 'Changed'])
        );
        $this->assertSame(
            1,
            Property::query()->whereKey($propertyIds['agency'])->update(['name' => 'Changed'])
        );
        $this->assertDatabaseHas('estateflow_properties', [
            'id' => $propertyIds['other'],
            'name' => 'Other agency property',
        ]);
    }

    public function test_an_agency_cannot_delete_another_agencys_properties(): void
    {
        [$agency, $otherAgency] = $this->createAgencies();
        $propertyIds = $this->createProperties($agency, $otherAgency);

        $this->actingAs($agency, 'user');

        $this->assertSame(0, Property::query()->whereKey($propertyIds['other'])->delete());
        $this->assertSame(1, Property::query()->whereKey($propertyIds['agency'])->delete());
        $this->assertDatabaseHas('estateflow_properties', ['id' => $propertyIds['other']]);
    }

    public function test_a_created_property_belongs_only_to_the_authenticated_agency(): void
    {
        [$agency, $otherAgency] = $this->createAgencies();

        $this->actingAs($agency, 'user');

        $property = Property::create(['name' => 'New agency property']);

        $this->assertSame($agency->id, $property->agency_id);
        $this->assertNotNull(Property::query()->find($property->id));

        $this->actingAs($otherAgency, 'user');

        $this->assertNull(Property::query()->find($property->id));
    }

    /**
     * @return array{0: User, 1: User}
     */
    private function createAgencies(): array
    {
        $role = Role::create([
            'name' => 'EstateFlow Tenant Test Role '.bin2hex(random_bytes(6)),
            'description' => 'EstateFlow tenant isolation test role.',
            'permission_type' => 'all',
        ]);

        $agencies = [];

        foreach (['Agency', 'Other Agency'] as $name) {
            $agencies[] = User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '-', $name)).'-'.bin2hex(random_bytes(4)).'@example.invalid',
                'password' => bcrypt('password'),
                'status' => 1,
                'role_id' => $role->id,
                'view_permission' => 'global',
            ]);
        }

        return $agencies;
    }

    /**
     * @return array{agency: int, other: int}
     */
    private function createProperties(User $agency, User $otherAgency): array
    {
        $timestamp = now();

        return [
            'agency' => DB::table('estateflow_properties')->insertGetId([
                'agency_id' => $agency->id,
                'name' => 'Agency property',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]),
            'other' => DB::table('estateflow_properties')->insertGetId([
                'agency_id' => $otherAgency->id,
                'name' => 'Other agency property',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]),
        ];
    }
}
