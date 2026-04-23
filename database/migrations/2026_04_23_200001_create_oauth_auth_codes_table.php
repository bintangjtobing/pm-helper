<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            // Hashed code value (we store sha256 of the raw code for the same
            // reason Sanctum stores hashed tokens).
            $table->string('code_hash', 64)->primary();
            $table->string('client_id', 64);
            $table->unsignedBigInteger('user_id');
            $table->string('redirect_uri');
            $table->string('scope')->default('mcp:*');
            // PKCE — MCP spec requires S256, we still record the method for clarity.
            $table->string('code_challenge', 128);
            $table->string('code_challenge_method', 16)->default('S256');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('expires_at');
            $table->index(['client_id', 'user_id']);

            $table->foreign('client_id')->references('client_id')->on('oauth_clients')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_auth_codes');
    }
};
