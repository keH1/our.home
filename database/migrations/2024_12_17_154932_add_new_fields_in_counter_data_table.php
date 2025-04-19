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
            $table->string('gis_id')->nullable();
            $table->string('info')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counter_data', function (Blueprint $table) {
            $table->dropColumn(['gis_id', 'info']);
        });
    }
};
