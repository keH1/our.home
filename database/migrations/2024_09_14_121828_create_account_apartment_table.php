<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('account_apartment', function (Blueprint $table) {
            $table->foreignId('account_id')->constrained('account_personal_numbers')->onDelete('cascade');
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->primary(['account_id', 'apartment_id']);
            $table->timestamps();
        });
    }
};
