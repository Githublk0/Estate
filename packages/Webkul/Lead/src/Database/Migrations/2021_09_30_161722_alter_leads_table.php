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

        DB::table('leads')->update([
            'leads.lead_pipeline_stage_id' => DB::raw($tablePrefix.'leads.lead_stage_id'),
        ]);

        if (! $isSqlite) {
            Schema::table('leads', function (Blueprint $table) use ($tablePrefix) {
                $table->dropForeign($tablePrefix.'leads_lead_stage_id_foreign');
                $table->dropColumn('lead_stage_id');
            });

            return;
        }

        // SQLite cannot drop a foreign-key column in place. Rebuild the table using the
        // current schema, preserving records while removing the obsolete lead_stage_id.
        Schema::disableForeignKeyConstraints();

        try {
            Schema::create('leads_sqlite_new', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('lead_value', 12, 4)->nullable();
                $table->boolean('status')->nullable();
                $table->text('lost_reason')->nullable();
                $table->datetime('closed_at')->nullable();

                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->integer('person_id')->unsigned();
                $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');

                $table->integer('lead_source_id')->unsigned();
                $table->foreign('lead_source_id')->references('id')->on('lead_sources')->onDelete('cascade');

                $table->integer('lead_type_id')->unsigned();
                $table->foreign('lead_type_id')->references('id')->on('lead_types')->onDelete('cascade');

                $table->integer('lead_pipeline_id')->unsigned()->nullable();
                $table->foreign('lead_pipeline_id')->references('id')->on('lead_pipelines')->onDelete('cascade');

                $table->integer('lead_pipeline_stage_id')->unsigned()->nullable();
                $table->foreign('lead_pipeline_stage_id')->references('id')->on('lead_pipeline_stages')->onDelete('set null');

                $table->date('expected_close_date')->nullable();
                $table->timestamps();
            });

            DB::table('leads_sqlite_new')->insertUsing(
                [
                    'id', 'title', 'description', 'lead_value', 'status', 'lost_reason', 'closed_at',
                    'user_id', 'person_id', 'lead_source_id', 'lead_type_id', 'lead_pipeline_id',
                    'lead_pipeline_stage_id', 'expected_close_date', 'created_at', 'updated_at',
                ],
                DB::table('leads')->select([
                    'id', 'title', 'description', 'lead_value', 'status', 'lost_reason', 'closed_at',
                    'user_id', 'person_id', 'lead_source_id', 'lead_type_id', 'lead_pipeline_id',
                    'lead_pipeline_stage_id', 'expected_close_date', 'created_at', 'updated_at',
                ])
            );

            Schema::drop('leads');
            Schema::rename('leads_sqlite_new', 'leads');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(DB::getTablePrefix().'leads_lead_pipeline_stage_id_foreign');
                $table->dropColumn('lead_pipeline_stage_id');

                $table->integer('lead_stage_id')->unsigned();
                $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');
            });

            return;
        }

        Schema::disableForeignKeyConstraints();

        try {
            Schema::create('leads_sqlite_legacy', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('lead_value', 12, 4)->nullable();
                $table->boolean('status')->nullable();
                $table->text('lost_reason')->nullable();
                $table->datetime('closed_at')->nullable();

                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->integer('person_id')->unsigned();
                $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');

                $table->integer('lead_source_id')->unsigned();
                $table->foreign('lead_source_id')->references('id')->on('lead_sources')->onDelete('cascade');

                $table->integer('lead_type_id')->unsigned();
                $table->foreign('lead_type_id')->references('id')->on('lead_types')->onDelete('cascade');

                $table->integer('lead_pipeline_id')->unsigned()->nullable();
                $table->foreign('lead_pipeline_id')->references('id')->on('lead_pipelines')->onDelete('cascade');

                $table->integer('lead_stage_id')->unsigned();
                $table->foreign('lead_stage_id')->references('id')->on('lead_stages')->onDelete('cascade');

                $table->date('expected_close_date')->nullable();
                $table->timestamps();
            });

            DB::table('leads_sqlite_legacy')->insertUsing(
                [
                    'id', 'title', 'description', 'lead_value', 'status', 'lost_reason', 'closed_at',
                    'user_id', 'person_id', 'lead_source_id', 'lead_type_id', 'lead_pipeline_id',
                    'lead_stage_id', 'expected_close_date', 'created_at', 'updated_at',
                ],
                DB::table('leads')->select([
                    'id', 'title', 'description', 'lead_value', 'status', 'lost_reason', 'closed_at',
                    'user_id', 'person_id', 'lead_source_id', 'lead_type_id', 'lead_pipeline_id',
                    'lead_pipeline_stage_id', 'expected_close_date', 'created_at', 'updated_at',
                ])
            );

            Schema::drop('leads');
            Schema::rename('leads_sqlite_legacy', 'leads');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
};
