<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('claims', function (Blueprint $table) {
            $table->boolean('is_paid')->nullable()->after('paid_service_id');
            $table->date('expectation_date')->nullable()->after('is_paid');

            if (Schema::hasColumn('claims', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            $table->unsignedBigInteger('client_id')->after('id');
            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('paid_service_id')->nullable()->change();
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });
    }
};
