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
            $table->foreignId('house_id')->constrained('houses');
            $table->string('account_number')->nullable();
            $table->string('account_id')->nullable();
            $table->string('gku_id')->nullable();
            $table->timestamps();
            $table->unique(['house_id', 'number'], '_apartment_number_uc');
        });
    }
};
