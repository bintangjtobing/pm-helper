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
        .hint{font-size:11px;color:#71717a;margin-top:6px;line-height:1.5}
        .drop{border:1px dashed #d4d4d8;border-radius:10px;background:#fafafa;padding:18px;text-align:center;cursor:pointer;transition:all 140ms ease}
        .drop:hover,.drop.dragover{border-color:#3b82f6;background:#eff6ff}
        .drop strong{display:block;font-size:13px;font-weight:600;color:#3f3f46;margin-bottom:4px}
        .drop span{font-size:11px;color:#71717a}
        .drop input[type=file]{display:none}
        .files{margin-top:10px;display:flex;flex-direction:column;gap:6px}
        .file-row{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 10px;background:#fafafa;border:1px solid #e4e4e7;border-radius:8px;font-size:12px;color:#3f3f46}
        .file-row .meta{display:flex;align-items:center;gap:8px;min-width:0;flex:1}
        .file-row .name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .file-row .size{color:#a1a1aa;font-size:11px;flex-shrink:0}
        .file-row button{width:auto;margin:0;padding:4px 8px;background:transparent;color:#a1a1aa;border:none;font-size:16px;line-height:1;cursor:pointer;border-radius:4px}
        .file-row button:hover{background:#fee2e2;color:#991b1b}
        .file-icon{flex-shrink:0;width:24px;height:24px;border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#fff;letter-spacing:0.5px}
        .file-icon.pdf{background:#dc2626}
        .file-icon.docx{background:#2563eb}
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

        <form method="POST" action="{{ route('public.feedback.store', $token) }}" autocomplete="off" enctype="multipart/form-data" id="feedback-form">
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

            <div class="field">
                <label>Attachments <span style="color:#a1a1aa;font-weight:400;">(optional)</span></label>
                <label class="drop" id="drop-zone" for="attachments">
                    <strong>Click to upload or drag &amp; drop</strong>
                    <span>PDF or DOCX · up to 25MB each · max 10 files</span>
                    <input type="file" id="attachments" name="attachments[]" multiple accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                </label>
                <div class="files" id="file-list"></div>
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

    <script>
        (function () {
            var MAX_BYTES = 25 * 1024 * 1024;
            var MAX_FILES = 10;
            var ALLOWED_EXT = ['pdf', 'docx'];

            var input = document.getElementById('attachments');
            var dropZone = document.getElementById('drop-zone');
            var list = document.getElementById('file-list');
            if (!input || !dropZone || !list) return;

            var files = [];

            function fmtSize(bytes) {
                if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
                if (bytes >= 1024) return Math.round(bytes / 1024) + ' KB';
                return bytes + ' B';
            }

            function extOf(name) {
                var i = name.lastIndexOf('.');
                return i >= 0 ? name.substring(i + 1).toLowerCase() : '';
            }

            function syncInput() {
                var dt = new DataTransfer();
                files.forEach(function (f) { dt.items.add(f); });
                input.files = dt.files;
            }

            function render() {
                list.innerHTML = '';
                files.forEach(function (f, idx) {
                    var ext = extOf(f.name);
                    var row = document.createElement('div');
                    row.className = 'file-row';

                    var meta = document.createElement('div');
                    meta.className = 'meta';

                    var icon = document.createElement('div');
                    icon.className = 'file-icon ' + (ext === 'docx' ? 'docx' : 'pdf');
                    icon.textContent = ext.toUpperCase();

                    var name = document.createElement('div');
                    name.className = 'name';
                    name.textContent = f.name;

                    var size = document.createElement('div');
                    size.className = 'size';
                    size.textContent = fmtSize(f.size);

                    meta.appendChild(icon);
                    meta.appendChild(name);
                    meta.appendChild(size);

                    var remove = document.createElement('button');
                    remove.type = 'button';
                    remove.setAttribute('aria-label', 'Remove file');
                    remove.innerHTML = '&times;';
                    remove.addEventListener('click', function () {
                        files.splice(idx, 1);
                        syncInput();
                        render();
                    });

                    row.appendChild(meta);
                    row.appendChild(remove);
                    list.appendChild(row);
                });
            }

            function add(fileList) {
                for (var i = 0; i < fileList.length; i++) {
                    var f = fileList[i];
                    if (files.length >= MAX_FILES) {
                        alert('You can attach up to ' + MAX_FILES + ' files.');
                        break;
                    }
                    var ext = extOf(f.name);
                    if (ALLOWED_EXT.indexOf(ext) === -1) {
                        alert('"' + f.name + '" was skipped — only PDF or DOCX files are allowed.');
                        continue;
                    }
                    if (f.size > MAX_BYTES) {
                        alert('"' + f.name + '" is larger than 25MB and was skipped.');
                        continue;
                    }
                    files.push(f);
                }
                syncInput();
                render();
            }

            input.addEventListener('change', function (e) {
                add(e.target.files);
            });

            ['dragenter', 'dragover'].forEach(function (evt) {
                dropZone.addEventListener(evt, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.add('dragover');
                });
            });
            ['dragleave', 'drop'].forEach(function (evt) {
                dropZone.addEventListener(evt, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.classList.remove('dragover');
                });
            });
            dropZone.addEventListener('drop', function (e) {
                if (e.dataTransfer && e.dataTransfer.files) {
                    add(e.dataTransfer.files);
                }
            });
        })();
    </script>
</body>
</html>
