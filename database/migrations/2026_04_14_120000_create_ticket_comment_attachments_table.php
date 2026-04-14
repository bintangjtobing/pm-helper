<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_comment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('ticket_comments')->cascadeOnDelete();
            $table->string('filename_stored');
            $table->string('filename_original');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();

            $table->index('comment_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_comment_attachments');
    }
};
