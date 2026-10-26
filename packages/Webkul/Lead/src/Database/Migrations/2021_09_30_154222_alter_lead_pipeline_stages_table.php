<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tablePrefix = DB::getTablePrefix();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('lead_pipeline_stages', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable();
            $table->string('name')->after('code')->nullable();
        });

        if ($isSqlite) {
            $pipelineStagesTable = $tablePrefix.'lead_pipeline_stages';
            $stagesTable = $tablePrefix.'lead_stages';

            DB::table('lead_pipeline_stages')->update([
                'code' => DB::raw("(SELECT {$stagesTable}.code FROM {$stagesTable} WHERE {$stagesTable}.id = {$pipelineStagesTable}.lead_stage_id)"),
                'name' => DB::raw("(SELECT {$stagesTable}.name FROM {$stagesTable} WHERE {$stagesTable}.id = {$pipelineStagesTable}.lead_stage_id)"),
            ]);
        } else {
            DB::table('lead_pipeline_stages')
                ->join('lead_stages', 'lead_pipeline_stages.lead_stage_id', '=', 'lead_stages.id')
                ->update([
                    'lead_pipeline_stages.code' => DB::raw($tablePrefix.'lead_stages.code'),
                    'lead_pipeline_stages.name' => DB::raw($tablePrefix.'lead_stages.name'),
                ]);
        }

        Schema::table('lead_pipeline_stages', function (Blueprint $table) use ($tablePrefix, $isSqlite) {
            // SQLite cannot drop a foreign key by name. Keep the legacy FK column on SQLite;
            // the application uses the copied code/name columns after this migration.
            if (! $isSqlite) {
                $table->dropForeign($tablePrefix.'lead_pipeline_stages_lead_stage_id_foreign');
                $table->dropColumn('lead_stage_id');
            }

            $table->unique(['code', 'lead_pipeline_id']);
            $table->unique(['name', 'lead_pipeline_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('lead_pipeline_stages', function (Blueprint $table) use ($isSqlite) {
            $table->dropUnique('lead_pipeline_stages_code_lead_pipeline_id_unique');
            $table->dropUnique('lead_pipeline_stages_name_lead_pipeline_id_unique');
            $table->dropColumn('code');
            $table->dropColumn('name');

            // On SQLite the original FK column was intentionally retained in up().
            if (! $isSqlite) {
                $table->integer('lead_stage_id')->unsigned();
                $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');
            }
        });
    }
};
