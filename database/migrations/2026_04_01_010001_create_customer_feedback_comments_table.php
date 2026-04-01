<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customer_feedback_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained('customer_feedbacks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();

            $table->index(['feedback_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_feedback_comments');
    }
};
