<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('goal_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('goal_periods')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();

            // Three sources of truth
            $table->decimal('system_score', 6, 2)->default(0);
            $table->decimal('self_score', 6, 2)->nullable();
            $table->decimal('final_score', 6, 2)->nullable();

            $table->text('self_narrative')->nullable();
            $table->text('supervisor_feedback')->nullable();

            // pending_self | pending_supervisor | completed | disputed
            $table->enum('status', ['pending_self', 'pending_supervisor', 'completed', 'disputed'])->default('pending_self');

            $table->timestamp('self_submitted_at')->nullable();
            $table->timestamp('supervisor_reviewed_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();

            $table->timestamps();

            $table->unique(['period_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index(['supervisor_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('goal_reviews');
    }
};
