<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meeting · PMHelper</title>
    <link rel="icon" href="/favicon.ico">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        html, body { margin: 0; padding: 0; height: 100%; background: #000; overflow: hidden; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        [x-cloak] { display: none !important; }
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
        .meet-iframe-wrap { flex: 1; width: 100%; min-height: 0; }
    </style>
</head>
<body>
<div
    x-data="meetTab()"
    x-init="init()"
    class="meet-root"
>
    <div class="meet-header">
        <div class="meet-header-left">
            <span x-show="isRecordingTranscript" class="meet-transcribe-status">
                <span class="meet-rec-dot"></span>
                <span>Transcribing…</span>
            </span>
            <span x-show="! isRecordingTranscript" style="font-size:11px;color:#6b7280;">Meeting in progress</span>
            <span class="meet-timer" x-text="timerLabel"></span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            <label style="font-size:11px;color:#9ca3af;">Language:</label>
            <select x-model="transcriptLanguage" x-on:change="applyLanguage()" class="meet-lang-select">
                <option value="en-US">English</option>
                <option value="id-ID">Bahasa Indonesia</option>
            </select>
            <button type="button" class="meet-end-btn" x-on:click="endMeeting()">End meeting</button>
        </div>
    </div>
    <div class="meet-iframe-wrap" x-ref="container"></div>
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

    function meetTab() {
        return {
            api: null,
            startTime: Date.now(),
            timerLabel: '00:00',
            timerHandle: null,
            transcriptChunks: [],
            isRecordingTranscript: false,
            participantIds: new Set(),
            hasFinalized: false,
            transcriptLanguage: localStorage.getItem('msgr.meetLang') || 'en-US',
            bc: null, // BroadcastChannel to main tab

            init() {
                // Open broadcast channel back to main PMHelper tab
                try { this.bc = new BroadcastChannel('pmhelper-meet'); } catch (e) { console.warn('[meet] BroadcastChannel unavailable', e); }

                // Announce meeting started so main tab flips user status to in_meeting
                this.broadcast('meeting:started', {
                    slug: MEET_CONFIG.slug,
                    role: MEET_CONFIG.role,
                    conversation_id: MEET_CONFIG.conversationId,
                });

                // Duration ticker
                this.timerHandle = setInterval(() => {
                    const sec = Math.floor((Date.now() - this.startTime) / 1000);
                    const m = String(Math.floor(sec / 60)).padStart(2, '0');
                    const s = String(sec % 60).padStart(2, '0');
                    this.timerLabel = `${m}:${s}`;
                }, 1000);

                // Initialize iframe once external_api.js is loaded
                this.waitForApi(() => this.initIframe());

                // If user closes tab abruptly (ctrl-W), still try to finalize
                window.addEventListener('beforeunload', () => this.finalize(true));
            },

            waitForApi(cb, attempt = 0) {
                if (window.JitsiMeetExternalAPI) { cb(); return; }
                if (attempt > 40) { alert('Jitsi Meet failed to load.'); window.close(); return; }
                setTimeout(() => this.waitForApi(cb, attempt + 1), 250);
            },

            initIframe() {
                this.api = new window.JitsiMeetExternalAPI('meet.digicrats.com', {
                    roomName: MEET_CONFIG.slug,
                    parentNode: this.$refs.container,
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
                this.wireEvents();
                setTimeout(() => this.applyLanguage(), 2000);
                if (MEET_CONFIG.role === 'starter') {
                    setTimeout(() => {
                        try { this.api.executeCommand('startTranscription'); } catch (e) { console.warn('[meet] startTranscription failed', e); }
                    }, 3000);
                }
            },

            wireEvents() {
                this.api.addListener('videoConferenceJoined', (e) => { if (e.id) this.participantIds.add(e.id); });
                this.api.addListener('participantJoined', (e) => { if (e.id) this.participantIds.add(e.id); });

                this.api.addListener('transcribingStatusChanged', (e) => { this.isRecordingTranscript = !! e.on; });

                this.api.addListener('transcriptionChunkReceived', (e) => {
                    if (MEET_CONFIG.role !== 'starter') return;
                    const id = e.data?.messageID || e.messageID || Math.random().toString(36);
                    const text = e.data?.final || e.data?.transcript?.[0]?.text || e.transcript || '';
                    const speaker = e.data?.participant?.name || e.participant?.name || 'Unknown';
                    if (! text || ! text.trim()) return;
                    const idx = this.transcriptChunks.findIndex(c => c.id === id);
                    const chunk = { id, speaker, text: text.trim(), ts: Date.now() };
                    if (idx >= 0) this.transcriptChunks[idx] = chunk;
                    else this.transcriptChunks.push(chunk);
                });

                this.api.addListener('videoConferenceLeft', () => this.finalize());
                this.api.addListener('readyToClose', () => this.finalize());
            },

            applyLanguage() {
                if (! this.api) return;
                try {
                    this.api.executeCommand('setSubtitles', true, false, this.transcriptLanguage);
                    localStorage.setItem('msgr.meetLang', this.transcriptLanguage);
                } catch (e) { console.warn('[meet] applyLanguage failed', e); }
            },

            endMeeting() {
                try { this.api?.executeCommand('hangup'); } catch (e) {}
                setTimeout(() => this.finalize(), 1500);
            },

            finalize(isUnload = false) {
                if (this.hasFinalized) return;
                this.hasFinalized = true;

                const durationSec = Math.floor((Date.now() - this.startTime) / 1000);
                const participantCount = Math.max(1, this.participantIds.size);

                if (MEET_CONFIG.role === 'starter' && MEET_CONFIG.conversationId) {
                    const transcript = this.transcriptChunks.map(c => `${c.speaker}: ${c.text}`).join('\n');
                    this.broadcast('meeting:ended', {
                        slug: MEET_CONFIG.slug,
                        conversation_id: MEET_CONFIG.conversationId,
                        meeting_message_id: MEET_CONFIG.meetingMessageId,
                        transcript,
                        duration_sec: durationSec,
                        participant_count: participantCount,
                    });
                } else {
                    // Joiner just tells main tab they left
                    this.broadcast('meeting:joiner_left', { slug: MEET_CONFIG.slug });
                }

                if (this.timerHandle) clearInterval(this.timerHandle);
                if (! isUnload) {
                    // Give broadcast a tick to flush before closing the tab
                    setTimeout(() => window.close(), 500);
                }
            },

            broadcast(type, payload) {
                try { this.bc?.postMessage({ type, payload, ts: Date.now() }); } catch (e) {}
            },
        };
    }
</script>
</body>
</html>
