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
        Schema::table('counter_histories', function (Blueprint $table) {
            $table->string('daily_consumption')->nullable()->change();
            $table->string('night_consumption')->nullable()->change();
            $table->string('peak_consumption')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counter_histories', function (Blueprint $table) {
            $table->double('daily_consumption')->nullable()->change();
            $table->double('night_consumption')->nullable()->change();
            $table->double('peak_consumption')->nullable()->change();
        });
    }
};
