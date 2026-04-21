<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting · PMHelper</title>
    <link rel="icon" href="/favicon.ico">
    <style>
        html, body { margin: 0; padding: 0; height: 100%; background: #000; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .meet-root { display: flex; flex-direction: column; height: 100vh; }
        .meet-header { height: 48px; background: #0d1117; border-bottom: 1px solid #1f2937; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; color: #e5e7eb; font-size: 13px; font-weight: 500; flex-shrink: 0; }
        .meet-header-left { display: flex; align-items: center; gap: 10px; }
        .meet-rec-dot { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; animation: meetPulse 1.5s ease-in-out infinite; }
        @keyframes meetPulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(0.9); } }
        .meet-timer { font-variant-numeric: tabular-nums; color: #9ca3af; }
        .meet-transcribe-status { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; color: #60a5fa; }
        .meet-lang-select { background: #1f2937; color: #e5e7eb; border: 1px solid #374151; border-radius: 6px; padding: 4px 8px; font-size: 11px; cursor: pointer; }
        .meet-end-btn { background: #ef4444; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background 0.15s; }
        .meet-end-btn:hover { background: #dc2626; }
        .meet-iframe-wrap {
            flex: 1;
            width: 100%;
            min-height: 0;
            position: relative;
            overflow: hidden;
        }
        .meet-iframe-wrap iframe {
            position: absolute !important;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100% !important;
            height: 100% !important;
            border: 0 !important;
        }
    </style>
</head>
<body>
<div class="meet-root">
    <div class="meet-header">
        <div class="meet-header-left">
            <span id="meet-transcribe-on" class="meet-transcribe-status" style="display:none;">
                <span class="meet-rec-dot"></span>
                <span>Transcribing…</span>
            </span>
            <span id="meet-transcribe-off" style="font-size:11px;color:#6b7280;">Meeting in progress</span>
            <span id="meet-timer" class="meet-timer">00:00</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <label style="font-size:11px;color:#9ca3af;">Language:</label>
            <select id="meet-lang" class="meet-lang-select">
                <option value="en-US">English</option>
                <option value="id-ID">Bahasa Indonesia</option>
            </select>
            <button type="button" id="meet-end" class="meet-end-btn">End meeting</button>
        </div>
    </div>
    <div id="meet-iframe-wrap" class="meet-iframe-wrap"></div>
</div>

<script src="https://meet.digicrats.com/external_api.js"></script>
<script>
    @php
        $safeRole = in_array($role, ['starter', 'joiner'], true) ? $role : 'joiner';
        $meetConfig = [
            'slug' => $slug,
            'role' => $safeRole,
            'conversationId' => $conversationId,
            'meetingMessageId' => $meetingMessageId,
            'userName' => $meetUser->name,
            'userEmail' => $meetUser->email,
            'userAvatar' => $meetUser->avatar_url,
        ];
    @endphp
    const MEET_CONFIG = {!! json_encode($meetConfig, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!};

    // ── Vanilla JS meeting controller ─────────────────────────────────
    (function () {
        const log = (...args) => console.log('[meet]', ...args);
        log('config', MEET_CONFIG);

        const state = {
            api: null,
            startTime: Date.now(),
            transcriptChunks: [],
            participantIds: new Set(),
            hasFinalized: false,
            timerHandle: null,
            bc: null,
        };

        // BroadcastChannel back to main PMHelper tab
        try { state.bc = new BroadcastChannel('pmhelper-meet'); } catch (e) { log('BroadcastChannel unavailable', e); }

        function broadcast(type, payload) {
            log('broadcast', type, payload);
            try { state.bc?.postMessage({ type, payload, ts: Date.now() }); }
            catch (e) { log('broadcast failed', e); }
        }

        // Timer
        const timerEl = document.getElementById('meet-timer');
        state.timerHandle = setInterval(() => {
            const sec = Math.floor((Date.now() - state.startTime) / 1000);
            const m = String(Math.floor(sec / 60)).padStart(2, '0');
            const s = String(sec % 60).padStart(2, '0');
            if (timerEl) timerEl.textContent = `${m}:${s}`;
        }, 1000);

        // Language select
        const langSel = document.getElementById('meet-lang');
        langSel.value = localStorage.getItem('msgr.meetLang') || 'en-US';
        langSel.addEventListener('change', () => {
            localStorage.setItem('msgr.meetLang', langSel.value);
            applyLanguage();
        });

        // End button
        document.getElementById('meet-end').addEventListener('click', endMeeting);

        // Announce meeting started to main tab so it flips user status
        broadcast('meeting:started', {
            slug: MEET_CONFIG.slug,
            role: MEET_CONFIG.role,
            conversation_id: MEET_CONFIG.conversationId,
        });

        // Wait for external_api.js to be ready, then bring up the iframe
        waitForApi(initIframe, 0);

        // If the tab is closed abruptly (Cmd+W), still try to finalize
        window.addEventListener('beforeunload', () => finalize(true));

        function waitForApi(cb, attempt) {
            if (window.JitsiMeetExternalAPI) { cb(); return; }
            if (attempt > 40) { alert('Jitsi Meet failed to load.'); window.close(); return; }
            setTimeout(() => waitForApi(cb, attempt + 1), 250);
        }

        function initIframe() {
            log('init iframe');
            const container = document.getElementById('meet-iframe-wrap');
            state.api = new window.JitsiMeetExternalAPI('meet.digicrats.com', {
                roomName: MEET_CONFIG.slug,
                parentNode: container,
                width: '100%',
                height: '100%',
                userInfo: {
                    displayName: MEET_CONFIG.userName || '',
                    email: MEET_CONFIG.userEmail || '',
                },
                configOverwrite: {
                    prejoinPageEnabled: false,
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    disableDeepLinking: true,
                    enableWelcomePage: false,
                    requireDisplayName: false,
                },
                interfaceConfigOverwrite: {
                    MOBILE_APP_PROMO: false,
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    DEFAULT_REMOTE_DISPLAY_NAME: 'Team member',
                },
            });

            wireEvents();
            setTimeout(applyLanguage, 2000);
            if (MEET_CONFIG.role === 'starter') {
                setTimeout(() => {
                    try { state.api.executeCommand('startTranscription'); }
                    catch (e) { log('startTranscription failed', e); }
                }, 3000);
            }
        }

        function wireEvents() {
            state.api.addListener('videoConferenceJoined', (e) => { log('joined', e); if (e.id) state.participantIds.add(e.id); });
            state.api.addListener('participantJoined', (e) => { log('participant joined', e); if (e.id) state.participantIds.add(e.id); });

            state.api.addListener('transcribingStatusChanged', (e) => {
                log('transcribing', e);
                document.getElementById('meet-transcribe-on').style.display = e.on ? 'inline-flex' : 'none';
                document.getElementById('meet-transcribe-off').style.display = e.on ? 'none' : 'inline';
            });

            state.api.addListener('transcriptionChunkReceived', (e) => {
                if (MEET_CONFIG.role !== 'starter') return;
                const id = e.data?.messageID || e.messageID || Math.random().toString(36);
                const text = e.data?.final || e.data?.transcript?.[0]?.text || e.transcript || '';
                const speaker = e.data?.participant?.name || e.participant?.name || 'Unknown';
                if (! text || ! text.trim()) return;
                const idx = state.transcriptChunks.findIndex(c => c.id === id);
                const chunk = { id, speaker, text: text.trim(), ts: Date.now() };
                if (idx >= 0) state.transcriptChunks[idx] = chunk;
                else state.transcriptChunks.push(chunk);
            });

            state.api.addListener('videoConferenceLeft', () => { log('conference left'); finalize(); });
            state.api.addListener('readyToClose', () => { log('ready to close'); finalize(); });
        }

        function applyLanguage() {
            if (! state.api) return;
            try { state.api.executeCommand('setSubtitles', true, false, langSel.value); }
            catch (e) { log('applyLanguage failed', e); }
        }

        function endMeeting() {
            log('end button clicked');
            try { state.api?.executeCommand('hangup'); } catch (e) { log('hangup failed', e); }
            // Finalize after a short grace period. hasFinalized guard protects
            // against readyToClose + this setTimeout both firing.
            setTimeout(() => finalize(), 1500);
        }

        function finalize(isUnload) {
            if (state.hasFinalized) return;
            state.hasFinalized = true;
            log('finalize, isUnload=' + !!isUnload);

            const durationSec = Math.floor((Date.now() - state.startTime) / 1000);
            const participantCount = Math.max(1, state.participantIds.size);

            if (MEET_CONFIG.role === 'starter' && MEET_CONFIG.conversationId) {
                const transcript = state.transcriptChunks.map(c => `${c.speaker}: ${c.text}`).join('\n');
                broadcast('meeting:ended', {
                    slug: MEET_CONFIG.slug,
                    conversation_id: MEET_CONFIG.conversationId,
                    meeting_message_id: MEET_CONFIG.meetingMessageId,
                    transcript,
                    duration_sec: durationSec,
                    participant_count: participantCount,
                });
            } else {
                broadcast('meeting:joiner_left', { slug: MEET_CONFIG.slug });
            }

            if (state.timerHandle) clearInterval(state.timerHandle);
            if (! isUnload) {
                // Give BroadcastChannel a tick to flush before closing the tab
                setTimeout(() => window.close(), 600);
            }
        }
    })();
</script>
</body>
</html>
