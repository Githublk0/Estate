<?php

namespace Tests\Feature\EstateFlow;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackfillPropertyAgenciesCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_backfills_from_user_id_and_leaves_ownerless_properties_unchanged(): void
    {
        $timestamp = now();

        $backfillableId = DB::table('estateflow_properties')->insertGetId([
            'user_id' => 71001,
            'name' => 'Property with owner',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $ownerlessId = DB::table('estateflow_properties')->insertGetId([
            'name' => 'Property without owner',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $alreadyAssignedId = DB::table('estateflow_properties')->insertGetId([
            'user_id' => 71002,
            'agency_id' => 72002,
            'name' => 'Property with existing agency',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $this->artisan('estateflow:backfill-agencies')
            ->expectsOutput('Backfilled 1 EstateFlow property; left 1 without user_id unchanged.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('estateflow_properties', [
            'id' => $backfillableId,
            'agency_id' => 71001,
        ]);
        $this->assertDatabaseHas('estateflow_properties', [
            'id' => $ownerlessId,
            'agency_id' => null,
        ]);
        $this->assertDatabaseHas('estateflow_properties', [
            'id' => $alreadyAssignedId,
            'agency_id' => 72002,
        ]);
    }
}
