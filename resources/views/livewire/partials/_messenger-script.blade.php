    <script>
        function messengerWidget() {
            const initialMyStatus = @json($this->myStatus);
            const todayStr = new Date().toISOString().split('T')[0];
            const tomorrowStr = new Date(Date.now() + 86400000).toISOString().split('T')[0];

            return {
                // ── Reactive state ──
                onlineUserIds: [],
                userStatuses: {},   // { userId: 'busy'|'in_meeting'|'on_leave'|null }
                isOtherTyping: false,
                typingTimeoutId: null,
                lastWhisperAt: 0,

                // Status menu state
                showStatusMenu: false,
                showLeaveForm: false,

                // Lightbox state
                lightboxSrc: null,
                lightboxName: '',
                lightboxDownload: '',
                statusMessage: initialMyStatus.status_message || '',
                leaveFrom: initialMyStatus.on_leave_from || todayStr,
                leaveUntil: initialMyStatus.on_leave_until || tomorrowStr,

                // ── Internal state (not exposed to Alpine reactivity) ──
                subscribedChannels: {},     // { conversationId: channel }
                presenceChannel: null,
                notificationAudio: null,
                notificationPermissionAsked: false,
                currentUserId: {{ (int) auth()->id() }},

                init() {
                    // Seed userStatuses with my own status so it renders
                    // before presence channel returns the full list.
                    if (initialMyStatus.status) {
                        this.userStatuses[this.currentUserId] = initialMyStatus.status;
                    }

                    // Preload notification sound
                    try {
                        this.notificationAudio = new Audio('/sounds/messenger-notification.mp3');
                        this.notificationAudio.preload = 'auto';
                        this.notificationAudio.volume = 0.7;
                    } catch (e) {
                        console.warn('[messenger] Failed to load notification sound', e);
                    }

                    // Browser auto-scroll on send
                    window.addEventListener('messenger:message-sent', () => {
                        this.$nextTick(() => this.scrollMessagesToBottom());
                    });

                    // Open Jitsi meeting in a new tab when starter clicks the button
                    window.addEventListener('messenger:open-meeting', (e) => {
                        const url = e.detail?.url;
                        if (url) {
                            window.open(url, '_blank', 'noopener,noreferrer');
                        }
                    });

                    // Initial scroll
                    this.$nextTick(() => this.scrollMessagesToBottom());

                    // Wait for Echo to be ready, then bootstrap subscriptions
                    this.waitForEcho().then(() => {
                        this.subscribeToPresence();
                        this.syncConversationSubscriptions();

                        // Re-sync subscriptions whenever Livewire re-renders the component
                        // (e.g. after sending a message, opening a new chat, etc.)
                        if (window.Livewire) {
                            window.Livewire.hook('message.processed', () => {
                                this.syncConversationSubscriptions();
                                this.$nextTick(() => this.scrollMessagesToBottom());
                            });
                        }
                    }).catch((e) => {
                        console.warn('[messenger] Echo not available, real-time disabled', e);
                    });
                },

                waitForEcho(maxAttempts = 20) {
                    return new Promise((resolve, reject) => {
                        let n = 0;
                        const tick = () => {
                            if (window.Echo) return resolve();
                            n++;
                            if (n >= maxAttempts) return reject(new Error('Echo not loaded'));
                            setTimeout(tick, 250);
                        };
                        tick();
                    });
                },

                // ──────────────────────────────────────────────────────────
                // Presence channel — global online status
                // ──────────────────────────────────────────────────────────
                subscribeToPresence() {
                    if (this.presenceChannel) return;
                    try {
                        this.presenceChannel = window.Echo.join('messenger.online')
                            .here((users) => {
                                this.onlineUserIds = users.map(u => parseInt(u.id, 10));
                                users.forEach((u) => {
                                    this.userStatuses[parseInt(u.id, 10)] = u.status || null;
                                });
                            })
                            .joining((user) => {
                                const id = parseInt(user.id, 10);
                                if (! this.onlineUserIds.includes(id)) {
                                    this.onlineUserIds.push(id);
                                }
                                this.userStatuses[id] = user.status || null;
                            })
                            .leaving((user) => {
                                const id = parseInt(user.id, 10);
                                this.onlineUserIds = this.onlineUserIds.filter(x => x !== id);
                            })
                            .listen('.status.changed', (e) => {
                                const id = parseInt(e.user_id, 10);
                                this.userStatuses[id] = e.status || null;
                                // Tell Livewire to refresh conversation list rows
                                this.$wire.emit('messenger:incoming-status', id);
                            });
                    } catch (e) {
                        console.warn('[messenger] presence channel failed', e);
                    }
                },

                // ──────────────────────────────────────────────────────────
                // Status helpers (used by Blade x-bind:class / x-text)
                // ──────────────────────────────────────────────────────────
                statusClassFor(userId) {
                    const id = parseInt(userId, 10);
                    const manual = this.userStatuses[id];
                    if (manual === 'on_leave') return 'is-on-leave';
                    if (manual === 'busy') return 'is-busy';
                    if (manual === 'in_meeting') return 'is-in-meeting';
                    if (this.onlineUserIds.includes(id)) return 'is-online';
                    return '';
                },

                statusLabelFor(userId) {
                    const id = parseInt(userId, 10);
                    const manual = this.userStatuses[id];
                    if (manual === 'on_leave') return 'On leave';
                    if (manual === 'busy') return 'Do not disturb';
                    if (manual === 'in_meeting') return 'In a meeting';
                    if (this.onlineUserIds.includes(id)) return 'Online';
                    return 'Offline';
                },

                isCurrentUserDND() {
                    const me = this.userStatuses[this.currentUserId];
                    return me === 'busy' || me === 'in_meeting';
                },

                // ──────────────────────────────────────────────────────────
                // Per-conversation private channel subscriptions
                // ──────────────────────────────────────────────────────────
                syncConversationSubscriptions() {
                    if (! window.Echo || ! this.$wire) return;
                    const conversations = this.$wire.get('conversations') || [];
                    const currentIds = conversations.map(c => parseInt(c.id, 10));

                    // Subscribe to any new conversations
                    currentIds.forEach((id) => {
                        if (! this.subscribedChannels[id]) {
                            this.subscribeToConversation(id);
                        }
                    });

                    // Unsubscribe from conversations no longer in the list
                    Object.keys(this.subscribedChannels).forEach((idStr) => {
                        const id = parseInt(idStr, 10);
                        if (! currentIds.includes(id)) {
                            try { window.Echo.leave('messenger.conversation.' + id); } catch (e) {}
                            delete this.subscribedChannels[id];
                        }
                    });
                },

                subscribeToConversation(conversationId) {
                    try {
                        const channel = window.Echo.private('messenger.conversation.' + conversationId)
                            .listen('.message.sent', (e) => this.onIncomingMessage(conversationId, e))
                            .listen('.message.edited', (e) => this.onIncomingEdit(e))
                            .listen('.message.deleted', (e) => this.onIncomingDelete(e))
                            .listen('.message.read', (e) => this.onIncomingRead(e))
                            .listen('.reaction.toggled', (e) => this.onIncomingReaction(e))
                            .listenForWhisper('typing', (data) => this.onWhisperTyping(conversationId, data));

                        this.subscribedChannels[conversationId] = channel;
                    } catch (e) {
                        console.warn('[messenger] failed to subscribe', conversationId, e);
                    }
                },

                // ──────────────────────────────────────────────────────────
                // Incoming event handlers — bridge to Livewire
                // ──────────────────────────────────────────────────────────
                onIncomingMessage(conversationId, e) {
                    const senderId = parseInt(e.sender_id, 10);
                    if (senderId === this.currentUserId) {
                        // Sender's own broadcast — UI already updated optimistically.
                        return;
                    }

                    // Tell Livewire to refresh data
                    this.$wire.emit('messenger:incoming-message', conversationId, parseInt(e.message_id, 10), senderId);

                    // Notify (sound + browser notification) when not actively viewing this conversation
                    const activeId = parseInt(this.$wire.get('activeConversationId') || 0, 10);
                    if (activeId !== conversationId || document.hidden) {
                        this.playNotificationSound();
                        this.showBrowserNotification(conversationId);
                    }
                },

                onIncomingEdit(e) {
                    this.$wire.emit('messenger:incoming-edit', parseInt(e.message_id, 10));
                },

                onIncomingDelete(e) {
                    this.$wire.emit('messenger:incoming-delete', parseInt(e.message_id, 10));
                },

                onIncomingRead(e) {
                    this.$wire.emit('messenger:incoming-read', parseInt(e.message_id, 10), parseInt(e.reader_id, 10));
                },

                onIncomingReaction(e) {
                    this.$wire.emit('messenger:incoming-reaction', parseInt(e.message_id, 10));
                },

                // ──────────────────────────────────────────────────────────
                // Typing indicator (whisper)
                // ──────────────────────────────────────────────────────────
                onTyping() {
                    // Throttle: 1 whisper per 2.5s (Pusher allows max 10/s/client).
                    const now = Date.now();
                    if (now - this.lastWhisperAt < 2500) return;
                    this.lastWhisperAt = now;

                    const activeId = parseInt(this.$wire.get('activeConversationId') || 0, 10);
                    if (! activeId) return;
                    const channel = this.subscribedChannels[activeId];
                    if (! channel) return;

                    try {
                        channel.whisper('typing', {
                            user_id: this.currentUserId,
                            ts: now,
                        });
                    } catch (e) { /* whisper rate-limit hits silently */ }
                },

                onWhisperTyping(conversationId, data) {
                    if (parseInt(data?.user_id, 10) === this.currentUserId) return;

                    const activeId = parseInt(this.$wire.get('activeConversationId') || 0, 10);
                    if (activeId !== conversationId) return;

                    this.isOtherTyping = true;
                    if (this.typingTimeoutId) clearTimeout(this.typingTimeoutId);
                    this.typingTimeoutId = setTimeout(() => {
                        this.isOtherTyping = false;
                    }, 3000);
                },

                // ──────────────────────────────────────────────────────────
                // Sound + browser notification
                // ──────────────────────────────────────────────────────────
                playNotificationSound() {
                    if (! this.notificationAudio) return;
                    if (this.isCurrentUserDND()) return; // mute when DND / in_meeting
                    try {
                        // Reset playhead so rapid messages still trigger sound
                        this.notificationAudio.currentTime = 0;
                        const p = this.notificationAudio.play();
                        if (p && typeof p.catch === 'function') {
                            p.catch(() => { /* autoplay blocked until first interaction */ });
                        }
                    } catch (e) { /* silent */ }
                },

                async ensureNotificationPermission() {
                    if (! ('Notification' in window)) return false;
                    if (Notification.permission === 'granted') return true;
                    if (Notification.permission === 'denied') return false;
                    if (this.notificationPermissionAsked) return false;
                    this.notificationPermissionAsked = true;
                    try {
                        const result = await Notification.requestPermission();
                        return result === 'granted';
                    } catch (e) { return false; }
                },

                async showBrowserNotification(conversationId) {
                    const allowed = await this.ensureNotificationPermission();
                    if (! allowed) return;
                    if (! document.hidden) return; // Only when window not focused

                    const conv = (this.$wire.get('conversations') || []).find(c => parseInt(c.id, 10) === conversationId);
                    const title = conv ? `New message from ${conv.other_name}` : 'New message';
                    const body  = conv ? (conv.last_preview || '') : '';
                    const icon  = conv ? conv.other_avatar : null;

                    try {
                        const n = new Notification(title, { body, icon, tag: 'messenger-' + conversationId });
                        n.onclick = () => {
                            window.focus();
                            this.$wire.emit('messenger:open-conversation', conversationId);
                            n.close();
                        };
                    } catch (e) { /* silent */ }
                },

                // ──────────────────────────────────────────────────────────
                // UI helpers
                // ──────────────────────────────────────────────────────────
                scrollMessagesToBottom() {
                    const el = this.$refs?.messagesContainer || document.querySelector('.msgr-messages');
                    if (el) {
                        el.scrollTop = el.scrollHeight;
                    }
                },
            };
        }
    </script>
