<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('objective')->nullable()->after('content');
            $table->text('expected_outcome')->nullable()->after('objective');
            $table->enum('impact', ['low', 'medium', 'high', 'critical'])->nullable()->after('expected_outcome');
            $table->string('request_department')->nullable()->after('impact');
            $table->enum('request_status', ['pending', 'under_review', 'approved', 'rejected'])->nullable()->after('request_department');
            $table->text('rejection_reason')->nullable()->after('request_status');
            $table->foreignId('reviewed_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'objective', 'expected_outcome', 'impact', 'request_department',
                'request_status', 'rejection_reason', 'reviewed_by', 'reviewed_at',
            ]);
        });
    }
};
