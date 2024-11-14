<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('house_notification', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained()->onDelete('cascade');
            $table->foreignId('notification_id')->constrained()->onDelete('cascade');
        });
    }
};
