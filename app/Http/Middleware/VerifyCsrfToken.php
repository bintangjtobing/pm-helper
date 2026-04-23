<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // OAuth endpoints used by MCP clients (Claude Desktop) — not browser
        // form submissions, so CSRF doesn't apply. /oauth/authorize POST DOES
        // need CSRF because it's our own consent form.
        'oauth/register',
        'oauth/token',
    ];
}
