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

            // SQLite cannot drop a named FK column in place. Rebuild the table with the
            // post-migration schema so the obsolete lead_stage_id constraint is removed.
            Schema::create('lead_pipeline_stages_sqlite_new', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code')->nullable();
                $table->string('name')->nullable();
                $table->integer('probability')->default(0);
                $table->integer('sort_order')->default(0);
                $table->integer('lead_pipeline_id')->unsigned();
                $table->foreign('lead_pipeline_id')->references('id')->on('lead_pipelines')->onDelete('cascade');
                $table->unique(['code', 'lead_pipeline_id']);
                $table->unique(['name', 'lead_pipeline_id']);
            });

            DB::table('lead_pipeline_stages_sqlite_new')->insertUsing(
                ['id', 'code', 'name', 'probability', 'sort_order', 'lead_pipeline_id'],
                DB::table('lead_pipeline_stages')->select(['id', 'code', 'name', 'probability', 'sort_order', 'lead_pipeline_id'])
            );

            Schema::drop('lead_pipeline_stages');
            Schema::rename('lead_pipeline_stages_sqlite_new', 'lead_pipeline_stages');

            return;
        }

        DB::table('lead_pipeline_stages')
            ->join('lead_stages', 'lead_pipeline_stages.lead_stage_id', '=', 'lead_stages.id')
            ->update([
                'lead_pipeline_stages.code' => DB::raw($tablePrefix.'lead_stages.code'),
                'lead_pipeline_stages.name' => DB::raw($tablePrefix.'lead_stages.name'),
            ]);

        Schema::table('lead_pipeline_stages', function (Blueprint $table) use ($tablePrefix) {
            $table->dropForeign($tablePrefix.'lead_pipeline_stages_lead_stage_id_foreign');
            $table->dropColumn('lead_stage_id');

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
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::create('lead_pipeline_stages_sqlite_legacy', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('probability')->default(0);
                $table->integer('sort_order')->default(0);
                $table->integer('lead_stage_id')->unsigned();
                $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');
                $table->integer('lead_pipeline_id')->unsigned();
                $table->foreign('lead_pipeline_id')->references('id')->on('lead_pipelines')->onDelete('cascade');
            });

            DB::table('lead_pipeline_stages_sqlite_legacy')->insertUsing(
                ['id', 'probability', 'sort_order', 'lead_stage_id', 'lead_pipeline_id'],
                DB::table('lead_pipeline_stages as pipeline_stages')
                    ->join('lead_stages as stages', function ($join) {
                        $join->on('pipeline_stages.code', '=', 'stages.code')
                            ->on('pipeline_stages.name', '=', 'stages.name');
                    })
                    ->select([
                        'pipeline_stages.id',
                        'pipeline_stages.probability',
                        'pipeline_stages.sort_order',
                        'stages.id',
                        'pipeline_stages.lead_pipeline_id',
                    ])
            );

            Schema::drop('lead_pipeline_stages');
            Schema::rename('lead_pipeline_stages_sqlite_legacy', 'lead_pipeline_stages');

            return;
        }

        Schema::table('lead_pipeline_stages', function (Blueprint $table) {
            $table->dropUnique('lead_pipeline_stages_code_lead_pipeline_id_unique');
            $table->dropUnique('lead_pipeline_stages_name_lead_pipeline_id_unique');
            $table->dropColumn('code');
            $table->dropColumn('name');

            $table->integer('lead_stage_id')->unsigned();
            $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');
        });
    }
};
