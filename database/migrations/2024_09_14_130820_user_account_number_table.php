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
//        Schema::create('user_account_personal_number', function (Blueprint $table) {
//            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
//            $table->foreignId('account_personal_number_id')->nullable()->constrained('account_personal_numbers')->onDelete('cascade');
//            $table->primary(['user_id', 'account_personal_number_id']);
//            $table->timestamps();
//        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
//        Schema::dropIfExists('user_account_personal_number');
    }
};
