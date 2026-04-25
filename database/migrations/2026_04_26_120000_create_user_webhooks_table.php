<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event')->default('qa_failed');
            $table->string('url', 1024)->nullable();
            $table->string('secret')->nullable();
            $table->json('project_ids')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_fired_at')->nullable();
            $table->unsignedSmallInteger('last_status')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'event']);
            $table->index(['event', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_webhooks');
    }
};
