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
            $table->string('number');  //account_id
            $table->string('els_id')->nullable();
            $table->string('gis_id')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('login')->nullable();
            $table->string('fio')->nullable();
            $table->string('debt')->nullable();

            //$table->string('gku_id')->nullable();

            $table->string('repair_els_id')->nullable();
            $table->string('repair_gis_id')->nullable();


            $table->string('one_c_id')->nullable();
            $table->boolean('is_active')->default(true);

            $table->foreignId('apartment_id')->nullable()->constrained('apartments')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
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
