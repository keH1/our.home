<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('claim_categories');
            $table->text('text');
            $table->boolean('is_active');
            $table->timestamp('finished_at')->nullable();
            $table->boolean('rating')->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }
};
