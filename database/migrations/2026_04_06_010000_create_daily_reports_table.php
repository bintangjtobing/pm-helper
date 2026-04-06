<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->date('report_date');
            $table->text('accomplished')->nullable();
            $table->text('plans')->nullable();
            $table->text('blockers')->nullable();
            $table->json('auto_summary')->nullable();
            $table->enum('status', ['draft', 'submitted', 'acknowledged'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'report_date', 'project_id'], 'daily_reports_user_date_project_unique');
            $table->index('status');
            $table->index('report_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
