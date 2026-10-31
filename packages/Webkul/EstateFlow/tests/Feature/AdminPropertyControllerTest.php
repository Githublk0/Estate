<?php

namespace Tests\Feature\EstateFlow;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Webkul\EstateFlow\Models\Event;
use Webkul\EstateFlow\Models\Property;
use Webkul\User\Models\Role;
use Webkul\User\Models\User;

class AdminPropertyControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        URL::forceRootUrl('http://localhost');
    }

    public function test_guests_cannot_open_the_property_inventory(): void
    {
        $this->get(route('admin.estateflow.properties.index'))
            ->assertRedirect(route('admin.session.create'));
    }

    public function test_an_agency_without_estateflow_permission_cannot_open_the_inventory(): void
    {
        $agency = $this->createAgency('Restricted Agency');
        $agency->role->update([
            'permission_type' => 'custom',
            'permissions' => ['dashboard'],
        ]);

        $this->actingAs($agency, 'user')
            ->get(route('admin.estateflow.properties.index'))
            ->assertUnauthorized();
    }

    public function test_an_agency_can_manage_its_properties_and_cannot_assign_another_agency(): void
    {
        $agency = $this->createAgency('Inventory Owner');
        $this->actingAs($agency, 'user');

        $this->get(route('admin.estateflow.properties.index'))
            ->assertOk()
            ->assertSee('No properties yet');

        $this->from(route('admin.estateflow.properties.create'))
            ->post(route('admin.estateflow.properties.store'), [
                'name' => '',
            ])
            ->assertRedirect(route('admin.estateflow.properties.create'))
            ->assertSessionHasErrors('name');

        $this->from(route('admin.estateflow.properties.create'))
            ->post(route('admin.estateflow.properties.store'), [
                'name' => 'Invalid property',
                'bedrooms' => 65536,
            ])
            ->assertRedirect(route('admin.estateflow.properties.create'))
            ->assertSessionHasErrors('bedrooms');

        $storeResponse = $this->post(route('admin.estateflow.properties.store'), [
            'name' => 'Pilot apartment',
            'address' => '1 Example Street',
            'price_cents' => 45000000,
            'bedrooms' => 2,
            'agency_id' => $agency->id + 1000,
        ]);

        $property = Property::withoutGlobalScopes()
            ->where('name', 'Pilot apartment')
            ->firstOrFail();

        $storeResponse
            ->assertRedirect(route('admin.estateflow.properties.show', $property->id))
            ->assertSessionHas('success', 'Property created.');

        $this->assertSame($agency->id, $property->agency_id);

        $this->get(route('admin.estateflow.properties.index'))
            ->assertOk()
            ->assertSee('Pilot apartment');

        $this->get(route('admin.estateflow.properties.show', $property->id))
            ->assertOk()
            ->assertSee('1 Example Street');

        $this->get(route('admin.estateflow.properties.edit', $property->id))
            ->assertOk();

        $this->put(route('admin.estateflow.properties.update', $property->id), [
            'name' => 'Updated apartment',
            'address' => '2 Example Street',
            'price_cents' => 46000000,
            'bedrooms' => 3,
            'agency_id' => $agency->id + 1000,
        ])
            ->assertRedirect(route('admin.estateflow.properties.show', $property->id))
            ->assertSessionHas('success', 'Property updated.');

        $property->refresh();
        $this->assertSame('Updated apartment', $property->name);
        $this->assertSame($agency->id, $property->agency_id);
        $this->assertSame(46000000, $property->price_cents);

        $this->patch(route('admin.estateflow.properties.status', $property->id))
            ->assertRedirect();
        $this->assertFalse($property->fresh()->active);

        $this->patch(route('admin.estateflow.properties.status', $property->id))
            ->assertRedirect();
        $this->assertTrue($property->fresh()->active);

        $this->delete(route('admin.estateflow.properties.destroy', $property->id))
            ->assertRedirect(route('admin.estateflow.properties.index'));

        $this->assertDatabaseMissing('estateflow_properties', ['id' => $property->id]);
    }

    public function test_an_agency_cannot_view_edit_update_or_delete_another_agencys_property(): void
    {
        $agency = $this->createAgency('Current Agency');
        $otherAgency = $this->createAgency('Other Agency');

        $propertyId = DB::table('estateflow_properties')->insertGetId([
            'agency_id' => $otherAgency->id,
            'name' => 'Private property',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($agency, 'user');

        $this->get(route('admin.estateflow.properties.index'))
            ->assertOk()
            ->assertDontSee('Private property');
        $this->get(route('admin.estateflow.properties.show', $propertyId))->assertNotFound();
        $this->get(route('admin.estateflow.properties.edit', $propertyId))->assertNotFound();
        $this->put(route('admin.estateflow.properties.update', $propertyId), [
            'name' => 'Tampered property',
        ])->assertNotFound();
        $this->patch(route('admin.estateflow.properties.status', $propertyId))->assertNotFound();
        $this->delete(route('admin.estateflow.properties.destroy', $propertyId))->assertNotFound();

        $this->assertDatabaseHas('estateflow_properties', [
            'id' => $propertyId,
            'name' => 'Private property',
            'agency_id' => $otherAgency->id,
            'active' => true,
        ]);
    }

    public function test_qualification_history_has_an_empty_state(): void
    {
        $agency = $this->createAgency('Empty Qualification Owner');

        $this->actingAs($agency, 'user')
            ->get(route('admin.estateflow.qualifications.index'))
            ->assertOk()
            ->assertSee('No qualification events are available for your account yet.');
    }

    public function test_qualification_history_is_limited_to_the_authenticated_agency(): void
    {
        $agency = $this->createAgency('Qualification Owner');
        $otherAgency = $this->createAgency('Other Qualification Owner');

        Event::create([
            'user_id' => $agency->id,
            'lead_id' => 901,
            'event_type' => 'lead.qualified',
            'payload' => ['result' => ['status' => 'Hot', 'score' => 100]],
        ]);

        Event::create([
            'user_id' => $otherAgency->id,
            'lead_id' => 902,
            'event_type' => 'lead.qualified',
            'payload' => ['result' => ['status' => 'Nurture', 'score' => 55]],
        ]);

        $this->actingAs($agency, 'user')
            ->get(route('admin.estateflow.qualifications.index'))
            ->assertOk()
            ->assertSee('Lead #901')
            ->assertSee('Hot')
            ->assertDontSee('Lead #902')
            ->assertDontSee('Nurture');
    }

    private function createAgency(string $name): User
    {
        $role = Role::create([
            'name' => $name.' role '.bin2hex(random_bytes(5)),
            'description' => 'EstateFlow admin feature test role.',
            'permission_type' => 'all',
        ]);

        return User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '-', $name)).'-'.bin2hex(random_bytes(5)).'@example.invalid',
            'password' => bcrypt('password'),
            'status' => 1,
            'role_id' => $role->id,
            'view_permission' => 'global',
        ]);
    }
}
