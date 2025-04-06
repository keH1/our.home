<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('1c_id')->nullable();
            $table->foreignId('house_id')->constrained('houses');
            $table->timestamps();
            $table->unique(['house_id', 'number'], '_apartment_number_uc');
        });
    }
};
