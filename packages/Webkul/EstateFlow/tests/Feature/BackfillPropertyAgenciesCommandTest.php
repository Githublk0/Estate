<?php

namespace Tests\Feature\EstateFlow;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackfillPropertyAgenciesCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_previews_backfillable_properties_without_changing_rows(): void
    {
        $timestamp = now();

        $backfillableId = DB::table('estateflow_properties')->insertGetId([
            'user_id' => 71003,
            'name' => 'Property to preview',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $ownerlessId = DB::table('estateflow_properties')->insertGetId([
            'name' => 'Property without owner',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $assignedOwnerlessId = DB::table('estateflow_properties')->insertGetId([
            'agency_id' => 72003,
            'name' => 'Property with assigned agency and no owner',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $this->artisan('estateflow:backfill-agencies --dry-run')
            ->expectsOutput('Dry run: 1 EstateFlow property would be backfilled.')
            ->expectsOutput('ID | Name | User ID')
            ->expectsOutput("{$backfillableId} | Property to preview | 71003")
            ->expectsOutput('Rows with NULL user_id: 2 (left unchanged).')
            ->assertExitCode(0);

        $this->assertDatabaseHas('estateflow_properties', [
            'id' => $backfillableId,
            'agency_id' => null,
        ]);
        $this->assertDatabaseHas('estateflow_properties', [
            'id' => $ownerlessId,
            'agency_id' => null,
        ]);
        $this->assertDatabaseHas('estateflow_properties', [
            'id' => $assignedOwnerlessId,
            'agency_id' => 72003,
        ]);
    }

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
