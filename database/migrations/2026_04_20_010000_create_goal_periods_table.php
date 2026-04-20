<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('goal_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['quarterly', 'monthly'])->default('quarterly');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('start_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('goal_periods');
    }
};
