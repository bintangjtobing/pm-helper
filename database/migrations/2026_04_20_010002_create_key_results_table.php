<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('key_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('goals')->cascadeOnDelete();
            $table->string('code')->nullable(); // e.g., "KR1"
            $table->string('title');
            $table->text('how_to_measure')->nullable();
            $table->decimal('weight', 5, 2)->default(0); // % — sum per goal = 100
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->default(0);
            $table->string('unit', 50)->nullable(); // %, count, IDR, USD, etc.
            $table->enum('direction', ['increase', 'decrease', 'maintain'])->default('increase');
            $table->enum('progress_mode', ['manual', 'auto', 'hybrid'])->default('manual');
            $table->string('auto_source')->nullable(); // tickets | daily_reports | weekly_reports | custom
            $table->json('auto_formula')->nullable(); // {"filter":{...},"aggregate":"count"}
            $table->string('alignment_note')->nullable(); // free-text alignment reference (e.g., "Amber O2-KR1")
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('goal_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('key_results');
    }
};
