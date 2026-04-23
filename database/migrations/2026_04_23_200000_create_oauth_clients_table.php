<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('oauth_clients', function (Blueprint $table) {
            // client_id is the opaque identifier Claude gets back from /oauth/register.
            $table->string('client_id', 64)->primary();
            $table->string('client_name');
            // redirect_uris is a JSON array; validated exact-match on /oauth/authorize
            // and /oauth/token to prevent open-redirect abuse.
            $table->json('redirect_uris');
            // RFC 7591: "none" means public client (PKCE only, no secret). We only
            // accept public clients since MCP desktop clients can't keep secrets.
            $table->string('token_endpoint_auth_method', 32)->default('none');
            $table->string('client_uri')->nullable();
            $table->string('software_id')->nullable();
            $table->string('software_version')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('last_used_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_clients');
    }
};
