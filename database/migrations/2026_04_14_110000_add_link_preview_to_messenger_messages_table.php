<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('messenger_messages', function (Blueprint $table) {
            $table->json('link_preview')->nullable()->after('body');
        });
    }

    public function down()
    {
        Schema::table('messenger_messages', function (Blueprint $table) {
            $table->dropColumn('link_preview');
        });
    }
};
