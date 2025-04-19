<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('counter_data', function (Blueprint $table) {
            $table->id();
            $table->string('account_one_c_id')->nullable();
            $table->timestamp('verification_to')->nullable();
            $table->string('counter_type')->nullable();
            $table->string('counter_seal')->nullable();
            $table->string('factory_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_worked')->default(true);
            $table->string('one_c_id')->unique();
            $table->string('els_id')->nullable();

            $table->timestamps();
        });
    }
};
