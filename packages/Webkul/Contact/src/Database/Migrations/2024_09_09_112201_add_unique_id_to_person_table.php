<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('unique_id')->nullable()->unique();
        });

        $tableName = DB::getTablePrefix().'persons';

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement(<<<SQL
                UPDATE {$tableName}
                SET unique_id = CAST(user_id AS TEXT) || '|' ||
                    CAST(organization_id AS TEXT) || '|' ||
                    JSON_EXTRACT(emails, '$[0].value') || '|' ||
                    JSON_EXTRACT(contact_numbers, '$[0].value')
            SQL);

            return;
        }

        DB::statement(<<<SQL
            UPDATE {$tableName}
            SET unique_id = CONCAT(
                user_id, '|',
                organization_id, '|',
                JSON_UNQUOTE(JSON_EXTRACT(emails, '$[0].value')), '|',
                JSON_UNQUOTE(JSON_EXTRACT(contact_numbers, '$[0].value'))
            )
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('unique_id');
        });
    }
};
