<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Send Feedback · {{ $project->name }}</title>
    <link rel="icon" href="/favicon.ico">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f4f4f5;color:#18181b;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{max-width:560px;width:100%;background:#fff;border-radius:16px;padding:32px 28px;box-shadow:0 1px 3px rgba(0,0,0,0.05),0 20px 40px -10px rgba(0,0,0,0.08)}
        .brand{font-size:11px;text-transform:uppercase;letter-spacing:1.4px;color:#71717a;margin-bottom:6px}
        h1{font-size:22px;font-weight:700;line-height:1.3;margin-bottom:6px}
        .subtitle{color:#52525b;font-size:14px;line-height:1.5;margin-bottom:24px}
        .field{margin-bottom:16px}
        label{display:block;font-size:12px;font-weight:600;color:#3f3f46;margin-bottom:6px;letter-spacing:0.2px}
        input,textarea{width:100%;padding:11px 13px;border:1px solid #e4e4e7;border-radius:10px;font-size:14px;font-family:inherit;background:#fafafa;color:#18181b;transition:all 140ms ease}
        input:focus,textarea:focus{outline:none;border-color:#3b82f6;background:#fff;box-shadow:0 0 0 3px rgba(59,130,246,0.12)}
        textarea{resize:vertical;min-height:120px}
        .hp{position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden}
        button{width:100%;margin-top:8px;padding:12px 18px;background:#18181b;color:#fff;border:none;border-radius:10px;font-weight:600;font-size:14px;cursor:pointer;transition:background 140ms ease}
        button:hover{background:#000}
        .footer{margin-top:20px;padding-top:18px;border-top:1px solid #e4e4e7;font-size:11px;color:#a1a1aa;text-align:center;line-height:1.6}
        .err{background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:10px 12px;border-radius:8px;font-size:13px;margin-bottom:14px}
        .err ul{margin-left:16px}
    </style>
</head>
<body>
    <div class="card">
        <div class="brand">Customer Feedback · {{ $project->name }}</div>
        <h1>Tell us what you think.</h1>
        <p class="subtitle">Found a bug, got an idea, or just want to say something? Drop it below — the project team gets it straight away.</p>

        @if($errors->any())
            <div class="err">
                <strong>Please fix the following:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public.feedback.store', $token) }}" autocomplete="off">
            @csrf

            <div class="field">
                <label for="name">Your name</label>
                <input type="text" id="name" name="name" required maxlength="120" value="{{ old('name') }}">
            </div>

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required maxlength="160" value="{{ old('email') }}">
            </div>

            <div class="field">
                <label for="title">Subject</label>
                <input type="text" id="title" name="title" required maxlength="180" value="{{ old('title') }}" placeholder="One line that summarizes your feedback">
            </div>

            <div class="field">
                <label for="description">Details</label>
                <textarea id="description" name="description" required maxlength="5000" placeholder="What happened? What did you expect? Any links or steps?">{{ old('description') }}</textarea>
            </div>

            <div class="hp">
                <label for="website">Leave this empty</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit">Send feedback</button>
        </form>

        <div class="footer">
            Powered by PMHelper · Your feedback is sent privately to the {{ $project->name }} team. We never share your email.
        </div>
    </div>
</body>
</html>
