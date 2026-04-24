<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Thanks · {{ $project->name }}</title>
    <link rel="icon" href="/favicon.ico">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f4f4f5;color:#18181b;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
        .card{max-width:480px;width:100%;background:#fff;border-radius:16px;padding:40px 32px;box-shadow:0 1px 3px rgba(0,0,0,0.05),0 20px 40px -10px rgba(0,0,0,0.08);text-align:center}
        .check{width:56px;height:56px;border-radius:999px;background:#dcfce7;color:#15803d;display:inline-flex;align-items:center;justify-content:center;margin-bottom:18px;font-size:28px}
        h1{font-size:22px;font-weight:700;margin-bottom:10px}
        p{color:#52525b;font-size:14px;line-height:1.6;margin-bottom:8px}
        .footer{margin-top:20px;padding-top:18px;border-top:1px solid #e4e4e7;font-size:11px;color:#a1a1aa}
    </style>
</head>
<body>
    <div class="card">
        <div class="check">✓</div>
        <h1>Thanks — we got it.</h1>
        <p>Your feedback landed in the {{ $project->name }} inbox. If we need more detail, a team member will reach out to you.</p>
        <div class="footer">Powered by PMHelper</div>
    </div>
</body>
</html>
