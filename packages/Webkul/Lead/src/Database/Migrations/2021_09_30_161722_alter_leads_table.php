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

        Schema::table('leads', function (Blueprint $table) {
            $table->integer('lead_pipeline_stage_id')->after('lead_pipeline_id')->unsigned()->nullable();
            $table->foreign('lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('cascade');
        });

        DB::table('leads')
            ->update([
                'leads.lead_pipeline_stage_id' => DB::raw($tablePrefix.'leads.lead_stage_id'),
            ]);

        // SQLite cannot drop a foreign key by name. Retain the legacy stage column and
        // constraint there; the application reads the new pipeline-stage relation.
        if (! $isSqlite) {
            Schema::table('leads', function (Blueprint $table) use ($tablePrefix) {
                $table->dropForeign($tablePrefix.'leads_lead_stage_id_foreign');
                $table->dropColumn('lead_stage_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        Schema::table('leads', function (Blueprint $table) use ($isSqlite) {
            if (! $isSqlite) {
                $table->dropForeign(DB::getTablePrefix().'leads_lead_pipeline_stage_id_foreign');
            }

            $table->dropColumn('lead_pipeline_stage_id');

            // On SQLite up() intentionally retained the legacy lead_stage_id foreign key.
            if (! $isSqlite) {
                $table->integer('lead_stage_id')->unsigned();
                $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');
            }
        });
    }
};
