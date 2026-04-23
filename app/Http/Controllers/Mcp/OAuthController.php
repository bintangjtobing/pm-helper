<?php

namespace App\Http\Controllers\Mcp;

use App\Http\Controllers\Controller;
use App\Models\OauthAuthCode;
use App\Models\OauthClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class OAuthController extends Controller
{
    private const AUTH_CODE_TTL_SECONDS = 300;
    private const ACCESS_TOKEN_TTL_DAYS = 90;

    /**
     * RFC 8414 — Authorization Server Metadata.
     * MCP clients (Claude Desktop) fetch this first to discover endpoints + supported flows.
     */
    public function metadata(): JsonResponse
    {
        $base = url('/');

        return response()->json([
            'issuer' => $base,
            'authorization_endpoint' => $base . '/oauth/authorize',
            'token_endpoint' => $base . '/oauth/token',
            'registration_endpoint' => $base . '/oauth/register',
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code'],
            'code_challenge_methods_supported' => ['S256'],
            'token_endpoint_auth_methods_supported' => ['none'],
            'scopes_supported' => ['mcp:*'],
            'service_documentation' => $base . '/docs#mcp',
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * RFC 9728 — Protected Resource Metadata.
     * Points MCP clients at the authorization server for a given resource.
     */
    public function protectedResource(): JsonResponse
    {
        $base = url('/');
        return response()->json([
            'resource' => $base . '/api/mcp',
            'authorization_servers' => [$base],
            'scopes_supported' => ['mcp:*'],
            'bearer_methods_supported' => ['header'],
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * RFC 7591 — Dynamic Client Registration.
     * Claude Desktop POSTs here with its redirect URI + name. We mint a client_id.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'redirect_uris' => 'required|array|min:1',
            'redirect_uris.*' => 'required|string|url|max:500',
            'client_name' => 'nullable|string|max:255',
            'client_uri' => 'nullable|string|url|max:500',
            'software_id' => 'nullable|string|max:255',
            'software_version' => 'nullable|string|max:50',
            'token_endpoint_auth_method' => 'nullable|string|in:none',
            'grant_types' => 'nullable|array',
            'response_types' => 'nullable|array',
        ]);

        $clientId = 'mcp_' . Str::lower(Str::random(32));

        $client = OauthClient::create([
            'client_id' => $clientId,
            'client_name' => $data['client_name'] ?? 'MCP Client',
            'redirect_uris' => $data['redirect_uris'],
            'token_endpoint_auth_method' => 'none',
            'client_uri' => $data['client_uri'] ?? null,
            'software_id' => $data['software_id'] ?? null,
            'software_version' => $data['software_version'] ?? null,
        ]);

        return response()->json([
            'client_id' => $client->client_id,
            'client_name' => $client->client_name,
            'redirect_uris' => $client->redirect_uris,
            'token_endpoint_auth_method' => 'none',
            'grant_types' => ['authorization_code'],
            'response_types' => ['code'],
            'client_id_issued_at' => $client->created_at->timestamp,
        ], 201);
    }

    /**
     * GET /oauth/authorize — shows consent screen. Requires the user to be logged in
     * (handled by web middleware). Validates client_id + redirect_uri + PKCE challenge.
     */
    public function showConsent(Request $request)
    {
        $params = $request->validate([
            'response_type' => 'required|in:code',
            'client_id' => 'required|string',
            'redirect_uri' => 'required|url',
            'code_challenge' => 'required|string|min:43|max:128',
            'code_challenge_method' => 'required|in:S256',
            'state' => 'nullable|string|max:500',
            'scope' => 'nullable|string',
        ]);

        $client = OauthClient::find($params['client_id']);
        if (! $client) {
            abort(400, 'Unknown client_id.');
        }
        if (! $client->allowsRedirect($params['redirect_uri'])) {
            // Never redirect to an unregistered URI — open-redirect hazard.
            abort(400, 'redirect_uri is not registered for this client.');
        }

        return view('oauth.authorize', [
            'client' => $client,
            'params' => $params,
            'user' => $request->user(),
        ]);
    }

    /**
     * POST /oauth/authorize — user approved. Mint auth code, redirect back with code.
     */
    public function approve(Request $request): RedirectResponse
    {
        $params = $request->validate([
            'response_type' => 'required|in:code',
            'client_id' => 'required|string',
            'redirect_uri' => 'required|url',
            'code_challenge' => 'required|string|min:43|max:128',
            'code_challenge_method' => 'required|in:S256',
            'state' => 'nullable|string|max:500',
            'scope' => 'nullable|string',
            'decision' => 'required|in:approve,deny',
        ]);

        $client = OauthClient::find($params['client_id']);
        if (! $client || ! $client->allowsRedirect($params['redirect_uri'])) {
            abort(400, 'Invalid client or redirect_uri.');
        }

        if ($params['decision'] === 'deny') {
            return redirect()->away($this->appendQuery($params['redirect_uri'], [
                'error' => 'access_denied',
                'state' => $params['state'] ?? null,
            ]));
        }

        $rawCode = Str::random(64);
        OauthAuthCode::create([
            'code_hash' => hash('sha256', $rawCode),
            'client_id' => $client->client_id,
            'user_id' => $request->user()->id,
            'redirect_uri' => $params['redirect_uri'],
            'scope' => $params['scope'] ?? 'mcp:*',
            'code_challenge' => $params['code_challenge'],
            'code_challenge_method' => $params['code_challenge_method'],
            'expires_at' => now()->addSeconds(self::AUTH_CODE_TTL_SECONDS),
        ]);

        $client->forceFill(['last_used_at' => now()])->save();

        return redirect()->away($this->appendQuery($params['redirect_uri'], [
            'code' => $rawCode,
            'state' => $params['state'] ?? null,
        ]));
    }

    /**
     * POST /oauth/token — exchange authorization code for an access token.
     * Access tokens are Sanctum personal_access_tokens with ability mcp:*.
     */
    public function token(Request $request): JsonResponse
    {
        $data = $request->validate([
            'grant_type' => 'required|in:authorization_code',
            'code' => 'required|string',
            'redirect_uri' => 'required|url',
            'client_id' => 'required|string',
            'code_verifier' => 'required|string|min:43|max:128',
        ]);

        $authCode = OauthAuthCode::find(hash('sha256', $data['code']));
        if (! $authCode) {
            return $this->tokenError('invalid_grant', 'Authorization code not found.');
        }
        if (! $authCode->isValid()) {
            return $this->tokenError('invalid_grant', 'Authorization code expired or already used.');
        }
        if ($authCode->client_id !== $data['client_id']) {
            return $this->tokenError('invalid_grant', 'Code was issued to a different client.');
        }
        if ($authCode->redirect_uri !== $data['redirect_uri']) {
            return $this->tokenError('invalid_grant', 'redirect_uri does not match the one the code was issued for.');
        }

        // PKCE S256 verification: SHA-256 the verifier, base64url encode, compare to stored challenge.
        $expected = rtrim(strtr(base64_encode(hash('sha256', $data['code_verifier'], true)), '+/', '-_'), '=');
        if (! hash_equals($authCode->code_challenge, $expected)) {
            return $this->tokenError('invalid_grant', 'PKCE verification failed.');
        }

        $user = $authCode->user;
        if (! $user) {
            return $this->tokenError('invalid_grant', 'User no longer exists.');
        }

        $authCode->forceFill(['used_at' => now()])->save();

        $client = $authCode->client;
        $tokenName = 'mcp:oauth:' . ($client?->client_name ?: 'client') . ':' . $user->email;
        // Truncate to Sanctum's default name column length.
        $tokenName = Str::limit($tokenName, 150, '');

        $accessToken = $user->createToken($tokenName, ['mcp:*'], now()->addDays(self::ACCESS_TOKEN_TTL_DAYS));

        Log::info('MCP OAuth token issued', [
            'client_id' => $client?->client_id,
            'client_name' => $client?->client_name,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'access_token' => $accessToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TOKEN_TTL_DAYS * 86400,
            'scope' => $authCode->scope,
        ])->header('Cache-Control', 'no-store')->header('Pragma', 'no-cache');
    }

    private function tokenError(string $code, string $description): JsonResponse
    {
        return response()->json([
            'error' => $code,
            'error_description' => $description,
        ], 400)->header('Cache-Control', 'no-store')->header('Pragma', 'no-cache');
    }

    private function appendQuery(string $uri, array $params): string
    {
        $filtered = array_filter($params, fn ($v) => $v !== null && $v !== '');
        $glue = str_contains($uri, '?') ? '&' : '?';
        return $uri . $glue . http_build_query($filtered);
    }
}
