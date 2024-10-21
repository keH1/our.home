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
        Schema::create('account_personal_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('gku_id');
            $table->string('union_number');
            $table->foreignId('apartment_id')->nullable()->constrained('apartments')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_personal_numbers');
    }
};
