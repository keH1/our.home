<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('counter_data', function (Blueprint $table) {
            $table->string('shutdown_reason')->after('factory_number')->nullable();
            $table->integer('calibration_interval')->after('factory_number')->nullable();
            $table->foreignId('personal_number')->nullable()->constrained('account_personal_numbers');
            $table->timestamp('commissioning_date')->after('factory_number')->nullable();
            $table->timestamp('first_calibration_date')->after('factory_number')->nullable();
        });
        Schema::table('counter_histories', function (Blueprint $table) {
            $table->timestamp('last_checked_date')->after('peak_consumption')->nullable();
        });
    }
};
