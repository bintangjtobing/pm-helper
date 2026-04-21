{{-- Jitsi Meet embedded overlay - shared by floating and fullpage messenger --}}
<style>
    .msgr-meet-overlay {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: #000;
        display: flex;
        flex-direction: column;
    }
    .msgr-meet-overlay[hidden] { display: none; }
    .msgr-meet-overlay-header {
        height: 48px;
        background: #0d1117;
        border-bottom: 1px solid #1f2937;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        color: #e5e7eb;
        font-size: 13px;
        font-weight: 500;
        flex-shrink: 0;
    }
    .msgr-meet-overlay-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .msgr-meet-rec-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ef4444;
        animation: msgrMeetPulse 1.5s ease-in-out infinite;
    }
    @keyframes msgrMeetPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.9); }
    }
    .msgr-meet-overlay-timer {
        font-variant-numeric: tabular-nums;
        color: #9ca3af;
    }
    .msgr-meet-overlay-end {
        background: #ef4444;
        color: #fff;
        border: none;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }
    .msgr-meet-overlay-end:hover { background: #dc2626; }
    .msgr-meet-overlay-iframe {
        flex: 1;
        width: 100%;
        border: 0;
        min-height: 0;
    }
    .msgr-meet-overlay-transcript-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        color: #60a5fa;
    }
</style>

<div
    wire:ignore
    x-data="jitsiMeetOverlay()"
    x-init="init()"
    x-show="isOpen"
    x-cloak
    class="msgr-meet-overlay"
    @jitsi:open.window="open($event.detail)"
>
    <div class="msgr-meet-overlay-header">
        <div class="msgr-meet-overlay-header-left">
            <span x-show="isRecordingTranscript" class="msgr-meet-overlay-transcript-status">
                <span class="msgr-meet-rec-dot"></span>
                <span>Transcribing…</span>
            </span>
            <span x-show="! isRecordingTranscript" style="font-size:11px;color:#6b7280;">Meeting in progress</span>
            <span class="msgr-meet-overlay-timer" x-text="timerLabel"></span>
        </div>
        <button type="button" class="msgr-meet-overlay-end" x-on:click="endMeeting()">End meeting</button>
    </div>
    <div class="msgr-meet-overlay-iframe" x-ref="container"></div>
</div>

<script>
    function jitsiMeetOverlay() {
        return {
            isOpen: false,
            api: null,
            role: null,                // 'starter' | 'joiner'
            conversationId: null,
            slug: null,
            meetingMessageId: null,    // original "started a meeting" message id
            startTime: null,
            timerLabel: '00:00',
            timerHandle: null,
            transcriptChunks: [],      // raw chunks with speaker + text + timestamp
            isRecordingTranscript: false,
            participantIds: new Set(), // to count unique participants
            hasFinalized: false,

            init() {
                // Load the Jitsi external API script once, globally.
                if (! window.JitsiMeetExternalAPI && ! window.__jitsiExternalApiLoading) {
                    window.__jitsiExternalApiLoading = true;
                    const s = document.createElement('script');
                    s.src = 'https://meet.digicrats.com/external_api.js';
                    s.async = true;
                    document.head.appendChild(s);
                }
            },

            open(detail) {
                if (this.isOpen) {
                    // Another meeting is already active; refuse to stack
                    console.warn('[jitsi] overlay already open, ignoring new open');
                    return;
                }
                console.log('[jitsi] open()', detail);
                this.role = detail.role || 'joiner';
                this.conversationId = detail.conversation_id;
                this.slug = detail.slug;
                this.meetingMessageId = detail.meeting_message_id || null;
                this.startTime = Date.now();
                this.timerLabel = '00:00';
                this.transcriptChunks = [];
                this.isRecordingTranscript = false;
                this.participantIds = new Set();
                this.hasFinalized = false;

                this.isOpen = true;
                // Lock body scroll while meeting is up
                document.body.style.overflow = 'hidden';

                // Set user status to "In a meeting" so other team members see red dot
                try {
                    const componentId = this.getMessengerComponentId();
                    window.Livewire?.find(componentId)?.call('setMyStatus', 'in_meeting', 'In a meeting');
                } catch (e) { console.warn('[jitsi] setMyStatus failed', e); }

                // Start duration ticker
                this.timerHandle = setInterval(() => {
                    const sec = Math.floor((Date.now() - this.startTime) / 1000);
                    const m = String(Math.floor(sec / 60)).padStart(2, '0');
                    const s = String(sec % 60).padStart(2, '0');
                    this.timerLabel = `${m}:${s}`;
                }, 1000);

                // Wait for script, then init iframe
                this.$nextTick(() => this.waitForApi(() => this.initIframe(detail)));
            },

            waitForApi(cb, attempt = 0) {
                if (window.JitsiMeetExternalAPI) { cb(); return; }
                if (attempt > 40) {
                    alert('Jitsi Meet failed to load. Please check your connection.');
                    this.close();
                    return;
                }
                setTimeout(() => this.waitForApi(cb, attempt + 1), 250);
            },

            initIframe(detail) {
                const container = this.$refs.container;
                if (! container) return;
                container.innerHTML = ''; // clear any previous iframe

                this.api = new window.JitsiMeetExternalAPI('meet.digicrats.com', {
                    roomName: detail.slug,
                    parentNode: container,
                    width: '100%',
                    height: '100%',
                    userInfo: {
                        displayName: detail.user_name || '',
                        email: detail.user_email || '',
                    },
                    configOverwrite: {
                        prejoinPageEnabled: false,          // skip the "are you ready" screen
                        startWithAudioMuted: false,
                        startWithVideoMuted: false,
                        disableDeepLinking: true,           // no "open in app" banner
                        enableWelcomePage: false,
                        requireDisplayName: false,
                    },
                    interfaceConfigOverwrite: {
                        MOBILE_APP_PROMO: false,
                        SHOW_JITSI_WATERMARK: false,
                        SHOW_WATERMARK_FOR_GUESTS: false,
                        DEFAULT_REMOTE_DISPLAY_NAME: 'Team member',
                        TOOLBAR_BUTTONS: [
                            'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                            'fodeviceselection', 'hangup', 'chat', 'recording', 'livestreaming',
                            'etherpad', 'sharedvideo', 'settings', 'raisehand', 'videoquality',
                            'filmstrip', 'tileview', 'select-background', 'download', 'help',
                            'mute-everyone', 'mute-video-everyone',
                        ],
                    },
                });

                this.wireEvents();

                // Only the starter requests transcription be enabled for the room
                // (once it's on, it's on for everyone).
                if (this.role === 'starter') {
                    setTimeout(() => {
                        try {
                            this.api.executeCommand('startTranscription');
                        } catch (e) {
                            console.warn('[jitsi] startTranscription failed', e);
                        }
                    }, 3000); // give the conference a moment to fully join
                }
            },

            wireEvents() {
                if (! this.api) return;

                this.api.addListener('videoConferenceJoined', (e) => {
                    if (e.id) this.participantIds.add(e.id);
                });
                this.api.addListener('participantJoined', (e) => {
                    if (e.id) this.participantIds.add(e.id);
                });
                this.api.addListener('participantLeft', (e) => {
                    // keep in set — we want TOTAL unique participants, not current
                });

                this.api.addListener('transcribingStatusChanged', (e) => {
                    this.isRecordingTranscript = !! e.on;
                });

                // Every transcript chunk — only the starter saves (to avoid dupes)
                this.api.addListener('transcriptionChunkReceived', (e) => {
                    if (this.role !== 'starter') return;
                    // e.data shape varies by Jitsi version; guard.
                    const msg = e.data?.messageID || e.messageID || Math.random().toString(36);
                    const text = e.data?.final || e.data?.transcript?.[0]?.text || e.transcript || '';
                    const speaker = e.data?.participant?.name || e.participant?.name || 'Unknown';
                    if (! text || ! text.trim()) return;
                    // Dedupe by messageID — Jitsi emits partial then final with same ID
                    const idx = this.transcriptChunks.findIndex(c => c.id === msg);
                    const chunk = { id: msg, speaker, text: text.trim(), ts: Date.now() };
                    if (idx >= 0) this.transcriptChunks[idx] = chunk;
                    else this.transcriptChunks.push(chunk);
                });

                this.api.addListener('videoConferenceLeft', () => {
                    this.finalize();
                });
                this.api.addListener('readyToClose', () => {
                    this.finalize();
                });
            },

            endMeeting() {
                try { this.api?.executeCommand('hangup'); } catch (e) { /* noop */ }
                // Always finalize — even if Jitsi API never initialized (e.g. moderator
                // screen blocked us from ever joining). readyToClose will also call this;
                // the hasFinalized guard prevents double-post.
                setTimeout(() => this.finalize(), 1500);
            },

            finalize() {
                if (this.hasFinalized) return;
                this.hasFinalized = true;

                const durationSec = Math.floor((Date.now() - this.startTime) / 1000);
                const participantCount = Math.max(1, this.participantIds.size);

                // Only starter POSTs transcript + summary to server
                if (this.role === 'starter' && this.conversationId) {
                    const transcript = this.transcriptChunks
                        .map(c => `${c.speaker}: ${c.text}`)
                        .join('\n');

                    try {
                        const componentId = this.getMessengerComponentId();
                        console.log('[jitsi] calling finalizeMeeting', {
                            componentId, conversationId: this.conversationId, slug: this.slug,
                            transcriptLen: transcript.length, durationSec, participantCount,
                            meetingMessageId: this.meetingMessageId,
                        });
                        window.Livewire?.find(componentId)
                            ?.call('finalizeMeeting',
                                this.conversationId,
                                this.slug,
                                transcript,
                                durationSec,
                                participantCount,
                                this.meetingMessageId);
                    } catch (e) {
                        console.error('[jitsi] finalizeMeeting call failed', e);
                    }
                }

                this.close();
            },

            getMessengerComponentId() {
                // The overlay is rendered inside the messenger Livewire component,
                // so walk up from the overlay root to find the wrapping wire:id.
                const el = this.$el.closest('[wire\\:id]');
                return el?.getAttribute('wire:id');
            },

            close() {
                if (this.timerHandle) {
                    clearInterval(this.timerHandle);
                    this.timerHandle = null;
                }
                if (this.api) {
                    try { this.api.dispose(); } catch (e) { /* noop */ }
                    this.api = null;
                }
                if (this.$refs.container) this.$refs.container.innerHTML = '';
                this.isOpen = false;
                document.body.style.overflow = '';

                // Clear "In a meeting" status so user goes back to online
                try {
                    const componentId = this.getMessengerComponentId();
                    window.Livewire?.find(componentId)?.call('clearMyStatus');
                } catch (e) { console.warn('[jitsi] clearMyStatus failed', e); }
            },
        };
    }

    // Global click interceptor: any anchor with class msgr-meet-join-pill
    // should open the embed instead of navigating to a new tab.
    // Guard prevents double-binding if the script re-evaluates on Livewire update.
    if (! window.__msgrMeetPillInterceptorBound) {
        window.__msgrMeetPillInterceptorBound = true;
    document.addEventListener('click', function (e) {
        const pill = e.target.closest?.('.msgr-meet-join-pill');
        if (! pill) return;
        const href = pill.getAttribute('href');
        if (! href || ! /^https:\/\/meet\.(digicrats\.com|jit\.si)\/pmhelper-/i.test(href)) return;
        e.preventDefault();

        // Walk up from the pill to find the wrapping messenger Livewire component
        const livewireEl = pill.closest('[wire\\:id]');
        const componentId = livewireEl?.getAttribute('wire:id');
        if (componentId && window.Livewire?.find) {
            try {
                window.Livewire.find(componentId).call('joinJitsiMeeting', href);
            } catch (err) {
                console.error('[jitsi] joinJitsiMeeting call failed', err);
                window.open(href, '_blank', 'noopener,noreferrer');
            }
        } else {
            window.open(href, '_blank', 'noopener,noreferrer');
        }
    }, true);
    }
</script>
