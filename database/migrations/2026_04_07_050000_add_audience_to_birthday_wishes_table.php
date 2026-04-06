<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birthday_wishes', function (Blueprint $table) {
            $table->string('audience')->nullable()->after('tone');
        });
    }

    public function down(): void
    {
        Schema::table('birthday_wishes', function (Blueprint $table) {
            $table->dropColumn('audience');
        });
    }
};
