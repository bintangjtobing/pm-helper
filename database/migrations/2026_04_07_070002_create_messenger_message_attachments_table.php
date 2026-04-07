<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messenger_message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messenger_messages')->cascadeOnDelete();
            $table->string('filename_stored');
            $table->string('filename_original');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();

            $table->index('message_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messenger_message_attachments');
    }
};
