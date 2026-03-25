<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_priorities', function (Blueprint $table) {
            $table->string('level', 10)->nullable()->after('name');
            $table->text('description')->nullable()->after('level');
            $table->text('examples')->nullable()->after('description');
            $table->string('action')->nullable()->after('examples');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_priorities', function (Blueprint $table) {
            $table->dropColumn(['level', 'description', 'examples', 'action']);
        });
    }
};
