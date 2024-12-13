<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('counter_data', function (Blueprint $table) {
            $table->string('union_number')->nullable();
            $table->string('gis_id')->nullable()->after('union_number');
            $table->foreign('gis_id')->references('gis_id')->on('apartments');
            $table->foreign('union_number')->references('union_number')->on('account_personal_numbers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counter_data', function (Blueprint $table) {
            $table->dropForeign(['union_number']);
            $table->dropForeign(['gis_id']);
            $table->dropColumn('union_number');
            $table->dropColumn('gis_id');
        });
    }
};
