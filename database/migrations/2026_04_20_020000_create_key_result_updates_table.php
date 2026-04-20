<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('key_result_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('key_result_id')->constrained('key_results')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // null for auto
            $table->decimal('value', 15, 2);
            $table->decimal('previous_value', 15, 2)->nullable();
            $table->text('note')->nullable();
            $table->enum('source', ['manual', 'auto', 'weekly_report'])->default('manual');
            $table->date('week_start')->nullable();
            $table->timestamps();

            $table->index(['key_result_id', 'created_at']);
            $table->index('week_start');
        });
    }

    public function down()
    {
        Schema::dropIfExists('key_result_updates');
    }
};
