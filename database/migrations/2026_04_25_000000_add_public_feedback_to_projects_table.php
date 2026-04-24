<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('public_feedback_token', 64)->nullable()->unique()->after('ticket_prefix');
            $table->boolean('public_feedback_enabled')->default(false)->after('public_feedback_token');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['public_feedback_token', 'public_feedback_enabled']);
        });
    }
};
