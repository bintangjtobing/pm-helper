<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customer_feedback_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_id')->constrained('customer_feedbacks')->cascadeOnDelete();
            $table->string('filename_stored');
            $table->string('filename_original');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();

            $table->index('feedback_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_feedback_attachments');
    }
};
