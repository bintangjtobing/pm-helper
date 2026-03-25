<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 10)->nullable()->after('type');
            $table->unsignedBigInteger('default_project_id')->nullable()->after('locale');

            $table->foreign('default_project_id')
                ->references('id')
                ->on('projects')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['default_project_id']);
            $table->dropColumn(['locale', 'default_project_id']);
        });
    }
};
