<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('claim_messages', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->foreignId('claim_id')->constrained('claims');
            $table->timestamps();
        });
    }
};
