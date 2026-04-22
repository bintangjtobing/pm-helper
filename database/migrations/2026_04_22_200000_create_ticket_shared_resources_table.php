<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ticket_shared_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('comment_id')->nullable()->constrained('ticket_comments')->cascadeOnDelete();
            $table->foreignId('attachment_id')->nullable()->constrained('ticket_comment_attachments')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // where it came from: 'description' | 'comment' | 'attachment'
            $table->string('source', 20);

            // docs | api | staging | design | repo | chat | video | image | file | default
            $table->string('kind', 20)->default('default');

            $table->text('url');
            $table->string('title')->nullable();
            $table->string('host')->nullable();

            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
            $table->index(['ticket_id', 'comment_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ticket_shared_resources');
    }
};
