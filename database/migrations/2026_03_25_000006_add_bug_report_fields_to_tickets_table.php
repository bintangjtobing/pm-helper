<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('steps_to_reproduce')->nullable()->after('content');
            $table->text('expected_behavior')->nullable()->after('steps_to_reproduce');
            $table->text('actual_behavior')->nullable()->after('expected_behavior');
            $table->string('environment')->nullable()->after('actual_behavior');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['steps_to_reproduce', 'expected_behavior', 'actual_behavior', 'environment']);
        });
    }
};
