<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messenger_message_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messenger_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('emoji', 16);
            $table->timestamps();

            $table->unique(['message_id', 'user_id']);
            $table->index('message_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messenger_message_reactions');
    }
};
