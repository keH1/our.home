<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('counter_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counter_name_id')->constrained('counter_data');
            $table->boolean('from_1c');
            $table->double('daily_consumption')->nullable();
            $table->double('night_consumption')->nullable();
            $table->double('peak_consumption')->nullable();
            $table->string('1c_id')->nullable();
            $table->timestamps();
        });
    }
};
