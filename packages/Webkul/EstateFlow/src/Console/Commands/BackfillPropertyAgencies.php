<?php

namespace Webkul\EstateFlow\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BackfillPropertyAgencies extends Command
{
    protected $signature = 'estateflow:backfill-agencies';

    protected $description = 'Set missing property agency IDs from non-null user IDs.';

    public function handle(): int
    {
        $ownerlessCount = DB::table('estateflow_properties')
            ->whereNull('agency_id')
            ->whereNull('user_id')
            ->count();

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
