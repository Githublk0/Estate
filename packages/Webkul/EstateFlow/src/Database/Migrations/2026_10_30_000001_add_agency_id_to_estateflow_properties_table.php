<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estateflow_properties', function (Blueprint $table) {
            $table->unsignedBigInteger('agency_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('estateflow_properties', function (Blueprint $table) {
            $table->dropIndex(['agency_id']);
            $table->dropColumn('agency_id');
        });
    }
};
