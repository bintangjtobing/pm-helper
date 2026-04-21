{{-- Meeting bridge: opens Jitsi in a new browser tab and forwards lifecycle
     events (started / ended / joiner_left) back from that tab to this tab via
     BroadcastChannel, so the Livewire messenger component can post the
     summary message, toggle in_meeting status, etc. Meeting tab lives at
     /dm/meet/{slug} and self-closes when the call ends. --}}
<div wire:ignore>
<script>
    (function () {
        if (window.__pmhelperMeetBridgeBound) return;
        window.__pmhelperMeetBridgeBound = true;

        function findMessengerComponent() {
            // Explicitly target the messenger Livewire component. Livewire 2's
            // $wire proxy does NOT expose .name directly — it forwards unknown
            // properties to either the data bag or a call() invocation. So use
            // $wire.__instance.name which is the Component class's name getter.
            if (! window.Livewire || typeof window.Livewire.all !== 'function') return null;
            try {
                return window.Livewire.all().find((c) => c?.__instance?.name === 'messenger') || null;
            } catch (e) {
                console.warn('[meet-bridge] findMessengerComponent error', e);
                return null;
            }
        }

        function callMessenger(method, ...args) {
            const comp = findMessengerComponent();
            if (! comp) {
                console.warn('[meet-bridge] messenger component not found, skipping ' + method);
                return;
            }
            try {
                comp.call(method, ...args);
            } catch (e) {
                console.warn('[meet-bridge] call ' + method + ' failed', e);
            }
        }

        // Open meeting in a new tab when Livewire dispatches jitsi:open
        window.addEventListener('jitsi:open', function (e) {
            const d = e.detail || {};
            if (! d.slug) return;
            const params = new URLSearchParams({
                role: d.role || 'joiner',
                convo: String(d.conversation_id || 0),
                msg: String(d.meeting_message_id || 0),
            });
            const url = '/dm/meet/' + encodeURIComponent(d.slug) + '?' + params.toString();
            const popup = window.open(url, '_blank', 'noopener,noreferrer');
            if (! popup) {
                alert('Popup blocked — please allow popups for PMHelper to start meetings.');
            }
        });

        // Click-intercept on any "Join meeting" pill to open in new tab
        document.addEventListener('click', function (e) {
            const pill = e.target.closest?.('.msgr-meet-join-pill');
            if (! pill) return;
            const href = pill.getAttribute('href');
            if (! href || ! /^https:\/\/meet\.(digicrats\.com|jit\.si)\/pmhelper-/i.test(href)) return;
            e.preventDefault();

            // Extract slug from the pill URL
            const m = href.match(/pmhelper-[a-z0-9]+/i);
            if (! m) { window.open(href, '_blank', 'noopener,noreferrer'); return; }

            // Figure out the current conversation id from the messenger Livewire state
            let convoId = 0;
            try {
                const comp = findMessengerComponent();
                convoId = parseInt(comp?.get?.('activeConversationId') || 0, 10) || 0;
            } catch (err) {}

            const params = new URLSearchParams({ role: 'joiner', convo: String(convoId) });
            window.open('/dm/meet/' + m[0] + '?' + params.toString(), '_blank', 'noopener,noreferrer');
        }, true);

        // BroadcastChannel: the /dm/meet tab shouts meeting lifecycle events here
        let bc;
        try { bc = new BroadcastChannel('pmhelper-meet'); } catch (err) { return; }

        bc.addEventListener('message', function (e) {
            const msg = e.data || {};
            console.log('[meet-bridge] received broadcast', msg);
            if (! msg.type) return;

            if (msg.type === 'meeting:started') {
                callMessenger('setMyStatus', 'in_meeting', 'In a meeting');
                return;
            }

            if (msg.type === 'meeting:ended') {
                const p = msg.payload || {};
                console.log('[meet-bridge] calling finalizeMeeting', p);
                callMessenger('finalizeMeeting',
                    parseInt(p.conversation_id, 10) || 0,
                    p.slug || '',
                    p.transcript || '',
                    parseInt(p.duration_sec, 10) || 0,
                    parseInt(p.participant_count, 10) || 1,
                    parseInt(p.meeting_message_id, 10) || null
                );
                callMessenger('clearMyStatus');
                return;
            }

            if (msg.type === 'meeting:joiner_left') {
                callMessenger('clearMyStatus');
                return;
            }
        });
        console.log('[meet-bridge] listening on BroadcastChannel pmhelper-meet');
    })();
</script>
</div>
