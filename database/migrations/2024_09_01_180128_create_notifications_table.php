<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('text');
            $table->string('category');
            $table->boolean('is_read');
            $table->string('action_type');
            $table->string('action_value');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }
};
