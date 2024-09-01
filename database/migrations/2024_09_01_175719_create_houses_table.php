<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('street');
            $table->string('number');
            $table->string('building')->nullable();
            $table->timestamps();
            $table->unique(['city', 'street', 'number'], '_house_number_uc');
        });
    }
};
