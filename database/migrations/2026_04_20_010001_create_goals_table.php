<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('goal_periods')->cascadeOnDelete();
            $table->enum('type', ['objective', 'kpi'])->default('objective');
            $table->enum('level', ['company', 'department', 'individual'])->default('individual');
            $table->foreignId('parent_id')->nullable()->constrained('goals')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code')->nullable(); // e.g., "O1", "O2" per user
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('weight', 5, 2)->default(0); // % — sum per user per period = 100
            $table->enum('status', ['draft', 'active', 'achieved', 'missed', 'cancelled'])->default('draft');
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['period_id', 'owner_id']);
            $table->index(['period_id', 'department_id']);
            $table->index(['period_id', 'level', 'status']);
            $table->index('parent_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('goals');
    }
};
