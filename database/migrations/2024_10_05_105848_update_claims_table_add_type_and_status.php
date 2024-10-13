<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->string('type')->after('id');
            $table->string('status')->after('is_active');
            $table->unsignedBigInteger('paid_service_id')->nullable()->after('category_id');

            $table->foreign('paid_service_id')
                  ->references('id')
                  ->on('paid_services')
                  ->onDelete('set null');
        });
    }
};
