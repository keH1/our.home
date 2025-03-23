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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title')->nullable();
            $table->string('text')->nullable();
            $table->boolean('is_active')->nullable();
        });
        Schema::table('notifications', function (Blueprint $table) {
           $table->foreignId('template_id')->after('user_id')
               ->nullable()->constrained('notification_templates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['notifications_template_id_foreign']);
            $table->dropColumn('template_id');
        });
    }
};
