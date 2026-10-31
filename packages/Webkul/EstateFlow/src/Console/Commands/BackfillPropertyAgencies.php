<?php

namespace Webkul\EstateFlow\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillPropertyAgencies extends Command
{
    protected $signature = 'estateflow:backfill-agencies {--dry-run : Preview the properties that would be backfilled}';

    protected $description = 'Set missing property agency IDs from non-null user IDs.';

    public function handle(): int
    {
        $ownerlessCount = DB::table('estateflow_properties')
            ->whereNull('user_id')
            ->count();

        if ($this->option('dry-run')) {
            $backfillableProperties = DB::table('estateflow_properties')
                ->select('id', 'name', 'user_id')
                ->whereNull('agency_id')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->get();

            $this->info(sprintf(
                'Dry run: %d EstateFlow %s would be backfilled.',
                $backfillableProperties->count(),
                Str::plural('property', $backfillableProperties->count()),
            ));

            $this->line('ID | Name | User ID');

            foreach ($backfillableProperties as $property) {
                $this->line(sprintf('%d | %s | %d', $property->id, $property->name, $property->user_id));
            }

            $this->line(sprintf('Rows with NULL user_id: %d (left unchanged).', $ownerlessCount));

            return self::SUCCESS;
        }

        $backfilledCount = DB::table('estateflow_properties')
            ->whereNull('agency_id')
            ->whereNotNull('user_id')
            ->update(['agency_id' => DB::raw('user_id')]);

        $this->info(sprintf(
            'Backfilled %d EstateFlow %s; left %d without user_id unchanged.',
            $backfilledCount,
            Str::plural('property', $backfilledCount),
            $ownerlessCount,
        ));

        return self::SUCCESS;
    }
}
