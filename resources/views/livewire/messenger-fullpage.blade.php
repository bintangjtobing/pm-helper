<div>
    @include('livewire.partials._messenger-styles')

    <style>
        /* ─────────────────────────────────────────────────────────────────
           Fullpage messenger overrides
           Reuses .msgr-* classes from _messenger-styles but repositions
           the layout into a three-column grid that fills the Filament page.
           ───────────────────────────────────────────────────────────────── */
        .msgr-fullpage-wrap {
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            grid-template-rows: minmax(0, 1fr);
            gap: 0;
            height: calc(100vh - 64px); /* minus filament topbar */
            background: #111827;
            overflow: hidden;
        }
        .msgr-fullpage-wrap.has-third {
            grid-template-columns: 320px minmax(0, 1fr) 320px;
        }
        .msgr-fullpage-aside {
            display: flex;
            flex-direction: column;
            border-right: 1px solid #1f2937;
            background: #111827;
            min-height: 0;
        }
        .msgr-fullpage-main {
            display: flex;
            flex-direction: column;
            background: #0b0f17;
            min-height: 0;
            position: relative;
        }
        .msgr-fullpage-third {
            display: flex;
            flex-direction: column;
            border-left: 1px solid #1f2937;
            background: #111827;
            min-height: 0;
        }
        .msgr-fullpage-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            padding: 24px;
            gap: 8px;
            text-align: center;
        }
        .msgr-fullpage-empty-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #1f2937;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #60a5fa;
        }
        .msgr-fullpage-empty-title {
            font-size: 14px;
            font-weight: 600;
            color: #e5e7eb;
        }
        .msgr-fullpage-empty-desc {
            font-size: 12px;
            color: #9ca3af;
        }

        /* Third panel header */
        .msgr-third-header {
            height: 56px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
            background: #0d1117;
            border-bottom: 1px solid #1f2937;
        }
        .msgr-third-title {
            font-size: 13px;
            font-weight: 600;
            color: #e5e7eb;
        }
        .msgr-third-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }
        .msgr-third-profile-avatar {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 12px;
            display: block;
            border: 3px solid #1f2937;
        }
        .msgr-third-profile-name {
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            color: #e5e7eb;
            margin-bottom: 2px;
        }
        .msgr-third-profile-username {
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 16px;
        }
        .msgr-third-section {
            border-top: 1px solid #1f2937;
            padding-top: 12px;
            margin-top: 12px;
        }
        .msgr-third-section-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .msgr-third-section-value {
            font-size: 13px;
            color: #e5e7eb;
        }
        .msgr-third-file-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            border-radius: 6px;
            transition: background 0.15s;
            cursor: pointer;
            text-decoration: none;
            color: #e5e7eb;
        }
        .msgr-third-file-item:hover {
            background: #1f2937;
        }
        .msgr-third-file-thumb {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            object-fit: cover;
            background: #374151;
            flex-shrink: 0;
        }
        .msgr-third-file-name {
            font-size: 12px;
            color: #e5e7eb;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .msgr-third-file-meta {
            font-size: 10px;
            color: #6b7280;
        }
        .msgr-third-empty {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            padding: 24px 12px;
        }

        /* Panel headers on fullpage use same look as floating .msgr-header */
        .msgr-fullpage-aside .msgr-header,
        .msgr-fullpage-main .msgr-header {
            border-radius: 0;
        }

        /* Scrollable body inside aside */
        .msgr-fullpage-aside .msgr-body {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }
        .msgr-fullpage-main .msgr-messages {
            flex: 1;
            min-height: 0;
        }

        /* Status menu repositioned for aside */
        .msgr-fullpage-aside .msgr-status-menu {
            left: 14px;
            top: 60px;
            right: auto;
        }

        @media (max-width: 900px) {
            .msgr-fullpage-wrap,
            .msgr-fullpage-wrap.has-third {
                grid-template-columns: 1fr;
                height: calc(100vh - 4rem);
            }
            .msgr-fullpage-aside {
                display: {{ $activeConversationId ? 'none' : 'flex' }};
                border-right: none;
            }
            .msgr-fullpage-main {
                display: {{ $activeConversationId ? 'flex' : 'none' }};
            }
            .msgr-fullpage-third {
                display: none;
            }
        }
    </style>

    <div x-data="messengerWidget()" x-init="init()"
         @messenger:reset-menus.window="showStatusMenu = false; showLeaveForm = false"
         @msgr-lightbox-open.window="lightboxSrc = $event.detail.src; lightboxName = $event.detail.name; lightboxDownload = $event.detail.download"
         @keydown.escape.window="lightboxSrc = null">

        {{-- Image Lightbox --}}
        <template x-if="lightboxSrc">
            <div class="msgr-lightbox" x-on:click="lightboxSrc = null" x-transition.opacity>
                <img x-bind:src="lightboxSrc" x-bind:alt="lightboxName" x-on:click.stop>
                <button type="button" class="msgr-lightbox-close" x-on:click="lightboxSrc = null">&times;</button>
                <a x-bind:href="lightboxDownload" class="msgr-lightbox-download" x-on:click.stop>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download
                </a>
            </div>
        </template>

        <div class="msgr-fullpage-wrap {{ $thirdPanelView ? 'has-third' : '' }}">

            {{-- ═══════════════════════════════════════════════════════════
                 LEFT ASIDE — conversation list / new-chat picker
                 ═══════════════════════════════════════════════════════════ --}}
            <aside class="msgr-fullpage-aside">

                {{-- Status menu backdrop --}}
                <div x-show="showStatusMenu" x-cloak x-on:click="showStatusMenu = false" class="msgr-status-backdrop"></div>

                {{-- Status dropdown --}}
                <div x-show="showStatusMenu" x-cloak class="msgr-status-menu">
                    <div class="msgr-status-menu-header">Set your status</div>
                    <button type="button" class="msgr-status-option" x-on:click="$wire.clearMyStatus(); showStatusMenu = false">
                        <span class="msgr-status-dot msgr-status-dot-inline is-online"></span>
                        <span>Online</span>
                    </button>
                    <button type="button" class="msgr-status-option" x-on:click="$wire.setMyStatus('busy', statusMessage || null); showStatusMenu = false">
                        <span class="msgr-status-dot msgr-status-dot-inline is-busy"></span>
                        <span>Do Not Disturb</span>
                    </button>
                    <button type="button" class="msgr-status-option" x-on:click="$wire.setMyStatus('in_meeting', statusMessage || null); showStatusMenu = false">
                        <span class="msgr-status-dot msgr-status-dot-inline is-in-meeting"></span>
                        <span>In a meeting</span><span class="msgr-status-option-meta">auto-clear 2h</span>
                    </button>
                    <button type="button" class="msgr-status-option" x-on:click="$wire.setMyStatus('lunch_break', statusMessage || null); showStatusMenu = false">
                        <span class="msgr-status-dot msgr-status-dot-inline is-lunch-break"></span>
                        <span>On lunch break</span><span class="msgr-status-option-meta">auto-clear 1h</span>
                    </button>
                    <button type="button" class="msgr-status-option" x-on:click="showLeaveForm = ! showLeaveForm">
                        <span class="msgr-status-dot msgr-status-dot-inline is-on-leave"></span>
                        <span>On leave…</span>
                    </button>
                    <div x-show="showLeaveForm" x-cloak class="msgr-status-leave-form">
                        <label style="font-size:11px;color:#9ca3af;">From</label>
                        <input type="date" x-model="leaveFrom">
                        <label style="font-size:11px;color:#9ca3af;">Until</label>
                        <input type="date" x-model="leaveUntil">
                        <button type="button" x-on:click="$wire.setMyStatus('on_leave', statusMessage || null, leaveFrom, leaveUntil); showLeaveForm = false; showStatusMenu = false">Set on leave</button>
                    </div>
                    <hr class="msgr-status-divider">
                    <input type="text" class="msgr-status-message-input" placeholder="What's your status?"
                           x-model="statusMessage" maxlength="80"
                           x-on:keydown.enter.prevent="$wire.updateMyStatusMessage(statusMessage); showStatusMenu = false">
                </div>

                {{-- Aside header --}}
                <div class="msgr-header">
                    @if($view === 'new_chat')
                        <div class="msgr-header-left">
                            <button type="button" class="msgr-icon-btn" wire:click="cancelNewChat" title="Back">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <div>
                                <div class="msgr-header-title">New message</div>
                                <div class="msgr-header-subtitle">Pick someone to chat with</div>
                            </div>
                        </div>
                    @else
                        <div class="msgr-header-left">
                            <div class="msgr-avatar-wrap" style="cursor: pointer;" x-on:click.stop="showStatusMenu = ! showStatusMenu" title="Set status">
                                <img src="{{ auth()->user()->avatar_url }}" alt="me" class="msgr-avatar">
                                <span class="msgr-status-dot" x-bind:class="statusClassFor({{ $this->currentUserId }})"></span>
                            </div>
                            <div>
                                <div class="msgr-header-title">
                                    Messaging
                                    @if($totalUnread > 0)
                                        <span class="msgr-unread-badge">{{ $totalUnread > 99 ? '99+' : $totalUnread }}</span>
                                    @endif
                                </div>
                                <div class="msgr-header-subtitle" x-text="statusLabelFor({{ $this->currentUserId }})"></div>
                            </div>
                        </div>
                        <div class="msgr-header-actions">
                            <button type="button" class="msgr-icon-btn" wire:click="openNewChatPicker" title="New message">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Aside body --}}
                <div class="msgr-body">
                    @if($view === 'new_chat')
                        <div class="msgr-search-bar">
                            <input type="text" class="msgr-search-input" placeholder="Search team members…" wire:model.debounce.300ms="newChatSearch" autofocus>
                        </div>
                        <div class="msgr-picker-list">
                            @forelse($userPickerResults as $u)
                                <div class="msgr-picker-item" wire:click="startConversationWith({{ $u['id'] }})">
                                    <img src="{{ $u['avatar'] }}" alt="" class="msgr-avatar">
                                    <div>
                                        <div class="msgr-picker-name">{{ $u['name'] }}</div>
                                        <div class="msgr-picker-username">{{ '@'.$u['username'] }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="msgr-list-empty">
                                    @if(strlen($newChatSearch) > 0)
                                        No users found.
                                    @else
                                        Type a name or username to search.
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    @else
                        <div class="msgr-search-bar">
                            <input type="text" class="msgr-search-input" placeholder="Search conversations…" wire:model.debounce.300ms="newChatSearch">
                        </div>
                        <div class="msgr-list">
                            @forelse($conversations as $c)
                                <div class="msgr-list-item {{ $activeConversationId === $c['id'] ? 'msgr-list-item-active' : '' }}"
                                     wire:click="openConversation({{ $c['id'] }})"
                                     style="{{ $activeConversationId === $c['id'] ? 'background:#1f2937;' : '' }}">
                                    <div class="msgr-avatar-wrap">
                                        <img src="{{ $c['other_avatar'] }}" alt="" class="msgr-avatar">
                                        <span class="msgr-status-dot" x-bind:class="statusClassFor({{ $c['other_id'] }})"></span>
                                    </div>
                                    <div class="msgr-list-item-content">
                                        <div class="msgr-list-item-row">
                                            <div class="msgr-list-name {{ $c['unread'] > 0 ? 'msgr-list-name-unread' : '' }}">
                                                {{ $c['other_name'] }}
                                            </div>
                                            <div class="msgr-list-time">{{ $c['last_at'] }}</div>
                                        </div>
                                        <div class="msgr-list-item-row">
                                            <div class="msgr-list-preview {{ $c['unread'] > 0 ? 'msgr-list-preview-unread' : '' }}">
                                                @if($c['last_is_self']) You: @endif
                                                @if($c['last_is_attachment'])
                                                    <span class="msgr-list-attachment-icon">📎</span>
                                                @endif
                                                {{ $c['last_preview'] }}
                                            </div>
                                            @if($c['unread'] > 0)
                                                <div class="msgr-list-unread-pill">{{ $c['unread'] > 99 ? '99+' : $c['unread'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="msgr-list-empty">
                                    <p>No conversations yet.</p>
                                    <p style="margin-top:8px; font-size:11px;">Click the pencil icon above to start a new chat.</p>
                                </div>
                            @endforelse
                        </div>
                    @endif
                </div>
            </aside>

            {{-- ═══════════════════════════════════════════════════════════
                 MAIN — active conversation OR empty state
                 ═══════════════════════════════════════════════════════════ --}}
            <main class="msgr-fullpage-main">
                @if($activeConversationId && $this->activeConversation)

                    {{-- Conversation header --}}
                    <div class="msgr-header">
                        <div class="msgr-header-left">
                            <div class="msgr-avatar-wrap" style="cursor:pointer;" wire:click="toggleThirdPanel('profile')">
                                <img src="{{ $this->activeConversation['other_avatar'] }}" alt="" class="msgr-avatar">
                                <span class="msgr-status-dot" x-bind:class="statusClassFor({{ $this->activeConversation['other_id'] }})"></span>
                            </div>
                            <div style="min-width:0; cursor:pointer;" wire:click="toggleThirdPanel('profile')">
                                <div class="msgr-header-title">{{ $this->activeConversation['other_name'] }}</div>
                                <div class="msgr-header-subtitle">
                                    <span x-text="statusLabelFor({{ $this->activeConversation['other_id'] }})"></span>
                                    @if(! empty($this->activeConversation['other_status_message']))
                                        · {{ $this->activeConversation['other_status_message'] }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="msgr-header-actions">
                            <button type="button" class="msgr-icon-btn" wire:click="toggleThirdPanel('files')" title="Shared files"
                                    style="{{ $thirdPanelView === 'files' ? 'background:#1f2937;color:#60a5fa;' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            </button>
                            <button type="button" class="msgr-icon-btn" wire:click="toggleThirdPanel('profile')" title="Profile"
                                    style="{{ $thirdPanelView === 'profile' ? 'background:#1f2937;color:#60a5fa;' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- OOO banner --}}
                    @if(($this->activeConversation['other_status'] ?? null) === 'on_leave')
                        <div class="msgr-leave-banner">
                            <strong>{{ $this->activeConversation['other_name'] }}</strong> is on leave
                            @if(! empty($this->activeConversation['other_on_leave_until']))
                                until {{ \Carbon\Carbon::parse($this->activeConversation['other_on_leave_until'])->format('d M Y') }}
                            @endif
                        </div>
                    @endif

                    {{-- Search bar --}}
                    <div class="msgr-search-bar">
                        <input type="text" class="msgr-search-input" placeholder="Search in this chat…" wire:model.debounce.400ms="searchQuery">
                    </div>

                    @if(count($searchResults) > 0)
                        <div class="msgr-search-results">
                            @foreach($searchResults as $sr)
                                <div class="msgr-search-result">
                                    <div class="msgr-search-result-meta">{{ $sr['sender'] }} · {{ $sr['time_label'] }}</div>
                                    <div class="msgr-search-result-body">{{ $sr['body'] }}</div>
                                </div>
                            @endforeach
                            <div style="padding:6px 14px; text-align:right;">
                                <button type="button" wire:click="clearSearch" style="background:transparent;border:none;color:#9ca3af;font-size:11px;cursor:pointer;">Clear</button>
                            </div>
                        </div>
                    @endif

                    {{-- Messages stream --}}
                    <div class="msgr-messages" x-ref="messagesContainer" id="msgr-messages-{{ $activeConversationId }}">
                        @if($hasMoreMessages)
                            <div class="msgr-load-more">
                                <button type="button" wire:click="loadMoreMessages">Load older messages</button>
                            </div>
                        @endif

                        @foreach($messages as $m)
                            @if($m['is_hidden_for_me']) @continue @endif
                            <div class="msgr-msg-row {{ $m['is_self'] ? 'msgr-msg-row-self' : 'msgr-msg-row-other' }}">
                                <div class="msgr-msg-bubble {{ $m['is_self'] ? 'msgr-msg-self' : 'msgr-msg-other' }}">
                                    @if($m['reply_to'])
                                        <div class="msgr-msg-reply-quote">
                                            <span class="msgr-msg-reply-quote-name">{{ $m['reply_to']['sender_name'] }}</span>
                                            {{ $m['reply_to']['body'] }}
                                        </div>
                                    @endif

                                    @if($editingMessageId === $m['id'])
                                        <div class="msgr-msg-content" style="background: #2a3138;">
                                            <textarea wire:model.defer="editingBody" class="msgr-composer-textarea" style="margin-bottom:6px;" rows="2"></textarea>
                                            <div style="display:flex; gap:6px; justify-content:flex-end;">
                                                <button type="button" wire:click="cancelEdit" style="background:transparent;border:1px solid #38434f;color:#9ca3af;padding:3px 10px;border-radius:6px;font-size:11px;cursor:pointer;">Cancel</button>
                                                <button type="button" wire:click="saveEdit" style="background:#3b82f6;border:none;color:#fff;padding:3px 10px;border-radius:6px;font-size:11px;cursor:pointer;">Save</button>
                                            </div>
                                            @error('editingBody') <div class="msgr-error">{{ $message }}</div> @enderror
                                        </div>
                                    @else
                                        @if($m['is_deleted_for_all'])
                                            <div class="msgr-msg-content msgr-msg-deleted">Message deleted</div>
                                        @elseif($m['body'])
                                            <div class="msgr-msg-content">{!! $m['rendered_body'] !!}@if($m['edited_at'])<span class="msgr-msg-edited-mark">(edited)</span>@endif</div>
                                        @endif

                                        @if(! empty($m['link_preview']) && ! $m['is_deleted_for_all'])
                                            <a href="{{ $m['link_preview']['url'] }}" target="_blank" rel="noopener" class="msgr-link-preview">
                                                @if(! empty($m['link_preview']['image']))
                                                    <img src="{{ $m['link_preview']['image'] }}" alt="" class="msgr-link-preview-image" loading="lazy" onerror="this.style.display='none'">
                                                @endif
                                                <div class="msgr-link-preview-body">
                                                    <div class="msgr-link-preview-domain">
                                                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                                        {{ $m['link_preview']['domain'] }}
                                                    </div>
                                                    <div class="msgr-link-preview-title">{{ $m['link_preview']['title'] }}</div>
                                                    @if(! empty($m['link_preview']['description']))
                                                        <div class="msgr-link-preview-desc">{{ $m['link_preview']['description'] }}</div>
                                                    @endif
                                                </div>
                                            </a>
                                        @endif

                                        @if(! empty($m['attachments']) && ! $m['is_deleted_for_all'])
                                            <div class="msgr-attachments">
                                                @foreach($m['attachments'] as $att)
                                                    @if($att['is_image'])
                                                        <img src="{{ $att['preview_url'] }}" alt="{{ $att['filename_original'] }}" class="msgr-attachment-image" loading="lazy"
                                                             x-on:click.stop="$dispatch('msgr-lightbox-open', { src: '{{ $att['preview_url'] }}', name: '{{ e($att['filename_original']) }}', download: '{{ $att['download_url'] }}' })">
                                                    @else
                                                        <a href="{{ $att['download_url'] }}" target="_blank" class="msgr-attachment-file">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                                            <div class="msgr-attachment-file-info">
                                                                <div class="msgr-attachment-file-name">{{ $att['filename_original'] }}</div>
                                                                <div class="msgr-attachment-file-size">
                                                                    {{ $att['size_bytes'] >= 1048576 ? round($att['size_bytes']/1048576, 1).' MB' : round($att['size_bytes']/1024, 1).' KB' }}
                                                                </div>
                                                            </div>
                                                        </a>
                                                        @php $isTranscript = preg_match('/\.(txt|vtt)$/i', $att['filename_original']); @endphp
                                                        @if($isTranscript)
                                                            <button type="button" class="msgr-summarize-btn" wire:click="summarizeTranscript({{ $m['id'] }}, {{ $att['id'] }})" wire:loading.attr="disabled" wire:target="summarizeTranscript({{ $m['id'] }},{{ $att['id'] }})">
                                                                <svg wire:loading.remove wire:target="summarizeTranscript({{ $m['id'] }},{{ $att['id'] }})" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                                <svg wire:loading wire:target="summarizeTranscript({{ $m['id'] }},{{ $att['id'] }})" class="msgr-spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                                <span wire:loading.remove wire:target="summarizeTranscript({{ $m['id'] }},{{ $att['id'] }})">Summarize with AI</span>
                                                                <span wire:loading wire:target="summarizeTranscript({{ $m['id'] }},{{ $att['id'] }})">Summarizing…</span>
                                                            </button>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    @endif

                                    @if(! empty($m['reactions']) && ! $m['is_deleted_for_all'])
                                        <div class="msgr-reactions">
                                            @foreach($m['reactions'] as $rg)
                                                <div class="msgr-reaction-chip {{ in_array($this->currentUserId, $rg['user_ids']) ? 'msgr-reaction-chip-active' : '' }}"
                                                     wire:click="toggleReaction({{ $m['id'] }}, '{{ $rg['emoji'] }}')">
                                                    <span>{{ $rg['emoji'] }}</span>
                                                    <span class="msgr-reaction-chip-count">{{ $rg['count'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="msgr-msg-meta">
                                        <span title="{{ $m['date_label'] }}">{{ $m['time_label'] }}</span>
                                        @if($m['is_self'] && ! $m['is_deleted_for_all'])
                                            @php
                                                $readByOther = collect($m['reads'])->contains(fn($r) => $r['user_id'] !== $this->currentUserId);
                                            @endphp
                                            @if($readByOther)
                                                <svg class="msgr-tick msgr-tick-read" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 11" fill="none" width="16" height="11" title="Read"><path d="M11.071.653a.457.457 0 00-.304-.102.493.493 0 00-.381.178l-6.19 7.636-2.405-2.405a.5.5 0 00-.353-.146.5.5 0 00-.354.146L.146 6.498a.5.5 0 000 .707l3.539 3.54a.5.5 0 00.354.146.5.5 0 00.354-.146l7.484-9.21a.5.5 0 00-.043-.671L11.071.653zm4.276.001a.457.457 0 00-.304-.102.493.493 0 00-.381.178L8.61 8.214 7.5 7.103l5.844-7.213a.457.457 0 00-.038-.617L12.391.273a.457.457 0 00-.305-.102.493.493 0 00-.381.178L4.43 9.225a.5.5 0 00.043.671l3.539 3.54a.5.5 0 00.354.146.5.5 0 00.354-.146L15.39.671a.5.5 0 00-.043-.018z" fill="currentColor"/></svg>
                                            @else
                                                <svg class="msgr-tick" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 11" fill="none" width="12" height="11" title="Sent"><path d="M11.071.653a.457.457 0 00-.304-.102.493.493 0 00-.381.178L4.196 8.365 1.79 5.96a.5.5 0 00-.353-.146.5.5 0 00-.354.146l-.937.938a.5.5 0 000 .707l3.539 3.54a.5.5 0 00.354.146.5.5 0 00.354-.146L11.877 1.83a.5.5 0 00-.043-.671L11.071.653z" fill="currentColor"/></svg>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                {{-- Hover action menu --}}
                                @if(! $m['is_deleted_for_all'])
                                    <div class="msgr-msg-actions" x-data="{ showPicker: false }">
                                        <button type="button" class="msgr-msg-action-btn" x-on:click="showPicker = !showPicker" title="React">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                        <button type="button" class="msgr-msg-action-btn" wire:click="setReplyTo({{ $m['id'] }})" title="Reply">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        </button>
                                        @if($m['is_self'] && $m['within_edit_window'])
                                            <button type="button" class="msgr-msg-action-btn" wire:click="startEdit({{ $m['id'] }})" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                        @endif
                                        <button type="button" class="msgr-msg-action-btn" wire:click="deleteMessageForMe({{ $m['id'] }})" title="Delete for me">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        @if($m['is_self'])
                                            <button type="button" class="msgr-msg-action-btn" wire:click="deleteMessageForEveryone({{ $m['id'] }})" title="Delete for everyone" style="color:#f87171;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            </button>
                                        @endif
                                        <div x-show="showPicker" x-on:click.outside="showPicker = false" class="msgr-emoji-picker" style="top: 28px; right: 0;">
                                            @foreach($this->allowedReactionEmojis as $emoji)
                                                <button type="button" x-on:click="showPicker = false; $wire.call('toggleReaction', {{ $m['id'] }}, '{{ $emoji }}')">{{ $emoji }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Typing indicator --}}
                    <div class="msgr-typing" x-show="isOtherTyping" style="display:none;">
                        <span>{{ $this->activeConversation['other_name'] ?? '' }} is typing</span>
                        <span class="msgr-typing-dot"></span>
                        <span class="msgr-typing-dot"></span>
                        <span class="msgr-typing-dot"></span>
                    </div>

                    {{-- Composer --}}
                    <div class="msgr-composer"
                         x-on:dragover.prevent="$el.classList.add('msgr-composer-dragover')"
                         x-on:dragleave.prevent="$el.classList.remove('msgr-composer-dragover')"
                         x-on:drop.prevent="
                             $el.classList.remove('msgr-composer-dragover');
                             const dt = $event.dataTransfer;
                             if (!dt || !dt.files.length) return;
                             const input = $el.querySelector('.msgr-hidden-input');
                             if (input) { input.files = dt.files; input.dispatchEvent(new Event('change', { bubbles: true })); }
                         "
                         x-on:paste="
                             const items = $event.clipboardData?.items;
                             if (! items) return;
                             const files = [];
                             for (const item of items) {
                                 if (item.kind === 'file') { const f = item.getAsFile(); if (f) files.push(f); }
                             }
                             if (! files.length) return;
                             $event.preventDefault();
                             const dt = new DataTransfer();
                             files.forEach(f => dt.items.add(f));
                             const input = $el.querySelector('.msgr-hidden-input');
                             if (input) { input.files = dt.files; input.dispatchEvent(new Event('change', { bubbles: true })); }
                         ">
                        @if($replyToMessageId)
                            @php $replyMsg = collect($messages)->firstWhere('id', $replyToMessageId); @endphp
                            @if($replyMsg)
                                <div class="msgr-composer-reply-bar">
                                    <div>Replying to <strong>{{ $replyMsg['sender_name'] }}</strong>: {{ \Illuminate\Support\Str::limit($replyMsg['body'] ?? '[attachment]', 60) }}</div>
                                    <button type="button" wire:click="cancelReply" style="background:transparent;border:none;color:#9ca3af;cursor:pointer;font-size:14px;">×</button>
                                </div>
                            @endif
                        @endif

                        @if(! empty($files))
                            <div class="msgr-composer-files">
                                @foreach($files as $idx => $file)
                                    <div class="msgr-composer-file-chip">
                                        @if(str_starts_with($file->getMimeType(), 'image/'))
                                            <img src="{{ $file->temporaryUrl() }}" style="width:24px;height:24px;object-fit:cover;border-radius:4px;margin-right:4px;">
                                        @endif
                                        <span>{{ $file->getClientOriginalName() }}</span>
                                        <span style="font-size:10px;color:#6b7280;margin-left:4px;">({{ number_format($file->getSize() / 1024, 0) }}KB)</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @error('files') <div class="msgr-error">{{ $message }}</div> @enderror
                        @error('newMessage') <div class="msgr-error">{{ $message }}</div> @enderror

                        <form wire:submit.prevent="sendMessage" class="msgr-composer-row">
                            <textarea wire:model.defer="newMessage" class="msgr-composer-textarea" placeholder="Write a message… (Enter to send)" rows="1"
                                      x-on:input="onTyping()"
                                      x-on:keydown.enter.prevent="$el.form.requestSubmit()"
                                      x-on:paste="
                                          const items = $event.clipboardData?.items;
                                          if (!items) return;
                                          for (const item of items) {
                                              if (item.type.startsWith('image/')) {
                                                  $event.preventDefault();
                                                  const file = item.getAsFile();
                                                  const dt = new DataTransfer();
                                                  dt.items.add(file);
                                                  const input = $el.closest('.msgr-composer').querySelector('.msgr-hidden-input');
                                                  if (input) { input.files = dt.files; input.dispatchEvent(new Event('change', { bubbles: true })); }
                                                  break;
                                              }
                                          }
                                      "></textarea>
                            <div class="msgr-composer-actions">
                                <button type="button" class="msgr-icon-btn" wire:click="startJitsiMeeting" wire:loading.attr="disabled" wire:target="startJitsiMeeting" title="Start video meeting (Jitsi)">
                                    <svg wire:loading.remove wire:target="startJitsiMeeting" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    <svg wire:loading wire:target="startJitsiMeeting" class="msgr-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                                <label class="msgr-icon-btn" title="Attach file">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <input type="file" wire:model="files" multiple accept="image/png,image/jpeg,image/jpg,image/gif,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.vtt" class="msgr-hidden-input">
                                </label>
                                <button type="submit" class="msgr-send-btn" wire:loading.attr="disabled" wire:target="sendMessage,files" title="Send">
                                    <svg wire:loading.remove wire:target="sendMessage,files" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    <svg wire:loading wire:target="sendMessage,files" class="msgr-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>

                @else
                    {{-- Empty state --}}
                    <div class="msgr-fullpage-empty">
                        <div class="msgr-fullpage-empty-icon">
                            <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <div class="msgr-fullpage-empty-title">Select a conversation</div>
                        <div class="msgr-fullpage-empty-desc">Pick someone on the left to start chatting, or click the pencil icon to start a new chat.</div>
                    </div>
                @endif
            </main>

            {{-- ═══════════════════════════════════════════════════════════
                 RIGHT ASIDE — profile or shared files (only when open)
                 ═══════════════════════════════════════════════════════════ --}}
            @if($thirdPanelView && $this->activeConversation)
                <aside class="msgr-fullpage-third">
                    <div class="msgr-third-header">
                        <div class="msgr-third-title">
                            {{ $thirdPanelView === 'profile' ? 'Profile' : 'Shared files' }}
                        </div>
                        <button type="button" class="msgr-icon-btn" wire:click="closeThirdPanel" title="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="msgr-third-body">
                        @if($thirdPanelView === 'profile')
                            <img src="{{ $this->activeConversation['other_avatar'] }}" alt="" class="msgr-third-profile-avatar">
                            <div class="msgr-third-profile-name">{{ $this->activeConversation['other_name'] }}</div>
                            @if(! empty($this->activeConversation['other_username']))
                                <div class="msgr-third-profile-username">{{ '@'.$this->activeConversation['other_username'] }}</div>
                            @endif

                            @php $otherUser = \App\Models\User::find($this->activeConversation['other_id']); @endphp
                            @if($otherUser)
                                @if($otherUser->email)
                                    <div class="msgr-third-section">
                                        <div class="msgr-third-section-label">Email</div>
                                        <div class="msgr-third-section-value">{{ $otherUser->email }}</div>
                                    </div>
                                @endif
                                @if($otherUser->department)
                                    <div class="msgr-third-section">
                                        <div class="msgr-third-section-label">Department</div>
                                        <div class="msgr-third-section-value">{{ $otherUser->department->name ?? '—' }}</div>
                                    </div>
                                @endif
                                @if($otherUser->position)
                                    <div class="msgr-third-section">
                                        <div class="msgr-third-section-label">Position</div>
                                        <div class="msgr-third-section-value">{{ $otherUser->position->name ?? '—' }}</div>
                                    </div>
                                @endif
                            @endif

                            <div class="msgr-third-section">
                                <div class="msgr-third-section-label">Status</div>
                                <div class="msgr-third-section-value" x-text="statusLabelFor({{ $this->activeConversation['other_id'] }})"></div>
                            </div>
                        @else
                            {{-- Shared files --}}
                            @php
                                $sharedAttachments = \App\Models\MessengerMessageAttachment::whereHas('message', function($q) use ($activeConversationId) {
                                    $q->where('conversation_id', $activeConversationId)
                                      ->whereNull('deleted_for_everyone_at');
                                })->orderByDesc('id')->limit(40)->get();
                            @endphp

                            @forelse($sharedAttachments as $att)
                                @php
                                    $isImage = str_starts_with($att->mime_type ?? '', 'image/');
                                    $downloadUrl = route('messenger.attachments.download', ['message' => $att->message_id, 'attachment' => $att->id]);
                                    $previewUrl = $isImage ? route('messenger.attachments.preview', ['message' => $att->message_id, 'attachment' => $att->id]) : null;
                                @endphp
                                <a href="{{ $downloadUrl }}" target="_blank" class="msgr-third-file-item">
                                    @if($isImage)
                                        <img src="{{ $previewUrl }}" alt="" class="msgr-third-file-thumb" loading="lazy">
                                    @else
                                        <div class="msgr-third-file-thumb" style="display:flex;align-items:center;justify-content:center;">
                                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                    @endif
                                    <div style="min-width:0;flex:1;">
                                        <div class="msgr-third-file-name">{{ $att->filename_original }}</div>
                                        <div class="msgr-third-file-meta">
                                            {{ $att->size_bytes >= 1048576 ? round($att->size_bytes/1048576, 1).' MB' : round($att->size_bytes/1024, 1).' KB' }}
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="msgr-third-empty">No shared files yet.</div>
                            @endforelse
                        @endif
                    </div>
                </aside>
            @endif
        </div>
    </div>

    @include('livewire.partials._messenger-script')

    {{-- Jitsi Meet embedded overlay (opens when user starts/joins a meet link) --}}
    @include('livewire.partials._messenger-meeting-overlay')
</div>
