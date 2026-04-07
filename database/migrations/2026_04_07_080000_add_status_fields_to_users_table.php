<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Manual status takes precedence over presence (online/away).
            // null = use presence; busy / in_meeting / on_leave = manual override.
            $table->string('status', 16)->nullable()->after('last_seen_at');
            $table->string('status_message', 80)->nullable()->after('status');
            // Auto-clear time for in_meeting (default 2h after set).
            $table->timestamp('status_until')->nullable()->after('status_message');
            // Date range for on_leave. on_leave_until expires the status.
            $table->date('on_leave_from')->nullable()->after('status_until');
            $table->date('on_leave_until')->nullable()->after('on_leave_from');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'status_message',
                'status_until',
                'on_leave_from',
                'on_leave_until',
            ]);
        });
    }
};
