<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('counter_data', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number')->unique();
            $table->foreignId('apartment_id')->constrained('apartments');
            $table->timestamp('verification_to');
            $table->string('counter_type');
            $table->string('counter_seal')->nullable();
            $table->string('factory_number')->nullable();
            $table->timestamps();
        });
    }
};
