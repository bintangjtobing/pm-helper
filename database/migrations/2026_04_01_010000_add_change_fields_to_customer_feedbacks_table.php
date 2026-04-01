<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customer_feedbacks', function (Blueprint $table) {
            $table->string('change_type')->nullable()->after('status');
            $table->json('proposed_data')->nullable()->after('change_type');
        });
    }

    public function down()
    {
        Schema::table('customer_feedbacks', function (Blueprint $table) {
            $table->dropColumn(['change_type', 'proposed_data']);
        });
    }
};
