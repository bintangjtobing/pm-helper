<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('messenger_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('messenger_conversations')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->foreignId('reply_to_id')->nullable()->constrained('messenger_messages')->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->json('hidden_for_user_ids')->nullable();
            $table->timestamp('deleted_for_everyone_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index('sender_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('messenger_messages');
    }
};
