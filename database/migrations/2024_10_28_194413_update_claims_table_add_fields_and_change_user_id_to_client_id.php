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
            $table->foreignId('account_id')->constrained('account_personal_numbers')->onDelete('set null');

            $table->unsignedBigInteger('paid_service_id')->nullable()->change();
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });
    }
};
