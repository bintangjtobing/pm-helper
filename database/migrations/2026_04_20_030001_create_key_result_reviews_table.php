<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('key_result_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_review_id')->constrained('goal_reviews')->cascadeOnDelete();
            $table->foreignId('key_result_id')->constrained('key_results')->cascadeOnDelete();

            // Snapshot of progress at review time
            $table->decimal('snapshot_current', 15, 2)->nullable();
            $table->decimal('snapshot_target', 15, 2)->nullable();

            $table->decimal('system_score', 6, 2)->default(0);
            $table->decimal('self_score', 6, 2)->nullable();
            $table->decimal('final_score', 6, 2)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['goal_review_id', 'key_result_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('key_result_reviews');
    }
};
