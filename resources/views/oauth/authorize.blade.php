<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorize {{ $client->client_name }} · PMHelper</title>
    @if(app()->bound('livewire'))
        @livewireStyles
    @endif
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #0b1220; color: #e5e7eb; min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 24px;
        }
        .card {
            width: 100%; max-width: 480px; background: #0f172a;
            border: 1px solid #1f2937; border-radius: 16px; padding: 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 28px; color: #94a3b8; font-size: 13px; }
        .brand-dot { width: 28px; height: 28px; border-radius: 8px; background: linear-gradient(135deg, #3b82f6, #6366f1); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 13px; }
        h1 { font-size: 20px; font-weight: 700; margin-bottom: 8px; color: #f3f4f6; }
        .subtitle { font-size: 13px; color: #9ca3af; line-height: 1.6; margin-bottom: 24px; }
        .client-badge { display: inline-flex; align-items: center; padding: 3px 10px; background: #1e293b; border: 1px solid #334155; border-radius: 999px; font-size: 12px; font-weight: 600; color: #e5e7eb; margin: 0 2px; }
        .identity { background: #111827; border: 1px solid #1f2937; border-radius: 10px; padding: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
        .avatar { width: 40px; height: 40px; border-radius: 999px; background: #3b82f6; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; flex-shrink: 0; overflow: hidden; }
        .avatar img { width: 100%; height: 100%; object-fit: cover; }
        .identity-name { font-size: 14px; font-weight: 600; color: #f3f4f6; }
        .identity-email { font-size: 12px; color: #9ca3af; margin-top: 2px; }
        .perms { background: #0c1322; border: 1px solid #1f2937; border-radius: 10px; padding: 14px 16px; margin-bottom: 24px; }
        .perms-title { font-size: 12px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px; }
        .perms ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }
        .perms li { font-size: 13px; color: #d1d5db; display: flex; align-items: flex-start; gap: 8px; line-height: 1.5; }
        .perms li::before { content: '✓'; color: #22c55e; font-weight: 700; flex-shrink: 0; }
        .actions { display: flex; gap: 10px; }
        .btn { flex: 1; padding: 11px 16px; border-radius: 10px; border: none; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.15s; font-family: inherit; }
        .btn-approve { background: #3b82f6; color: white; }
        .btn-approve:hover { background: #2563eb; }
        .btn-deny { background: transparent; color: #e5e7eb; border: 1px solid #334155; }
        .btn-deny:hover { background: #1e293b; }
        .footnote { font-size: 11px; color: #64748b; margin-top: 18px; text-align: center; line-height: 1.5; }
        .footnote a { color: #60a5fa; text-decoration: none; }
        .footnote a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="card">
    <div class="brand">
        <div class="brand-dot">P</div>
        <span>PMHelper · MCP authorization</span>
    </div>

    <h1>Authorize <span class="client-badge">{{ $client->client_name }}</span> to access PMHelper?</h1>
    <p class="subtitle">
        You'll be signing in as the account below. The app will act on your behalf in PMHelper — comments, tickets, and reports it creates will be attributed to you.
    </p>

    <div class="identity">
        <div class="avatar">
            @if($user->avatar_url ?? null)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
            @else
                {{ strtoupper(mb_substr($user->name, 0, 1)) }}
            @endif
        </div>
        <div>
            <div class="identity-name">{{ $user->name }}</div>
            <div class="identity-email">{{ $user->email }}</div>
        </div>
    </div>

    <div class="perms">
        <div class="perms-title">{{ $client->client_name }} will be able to</div>
        <ul>
            <li>List, view, and create tickets in projects you can access</li>
            <li>Update ticket status and add comments as you</li>
            <li>Read and reply to discussions in your projects</li>
            <li>Read your own daily reports and create new ones</li>
            <li>Look up users, projects, and ticket statuses</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('oauth.approve') }}">
        @csrf
        <input type="hidden" name="response_type" value="{{ $params['response_type'] }}">
        <input type="hidden" name="client_id" value="{{ $params['client_id'] }}">
        <input type="hidden" name="redirect_uri" value="{{ $params['redirect_uri'] }}">
        <input type="hidden" name="code_challenge" value="{{ $params['code_challenge'] }}">
        <input type="hidden" name="code_challenge_method" value="{{ $params['code_challenge_method'] }}">
        <input type="hidden" name="state" value="{{ $params['state'] ?? '' }}">
        <input type="hidden" name="scope" value="{{ $params['scope'] ?? 'mcp:*' }}">

        <div class="actions">
            <button type="submit" name="decision" value="deny" class="btn btn-deny">Deny</button>
            <button type="submit" name="decision" value="approve" class="btn btn-approve">Authorize</button>
        </div>
    </form>

    <p class="footnote">
        Not you? <a href="{{ url('/logout') }}">Sign out</a> and try again.
        You can revoke access any time from the <a href="{{ route('filament.pages.mcp-tokens') }}">MCP Tokens</a> page.
    </p>
</div>
</body>
</html>
