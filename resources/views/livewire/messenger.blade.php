<div>
    @include('livewire.partials._messenger-styles')

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

        {{-- ────────────────────────────────────────────────────────────────
             COLLAPSED STATE — bottom bar acting as launcher
             ──────────────────────────────────────────────────────────────── --}}
        @if(! $isOpen)
            <div class="msgr-collapsed" wire:click="toggleOpen">
                <div class="msgr-header-left">
                    <div class="msgr-avatar-wrap">
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
                    </div>
                </div>
                <div class="msgr-header-actions">
                    <button type="button" class="msgr-icon-btn" x-on:click.stop="$wire.call('openNewChatPicker')" title="New message">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button type="button" class="msgr-icon-btn" title="Open">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- ────────────────────────────────────────────────────────────────
             EXPANDED STATE — full panel
             ──────────────────────────────────────────────────────────────── --}}
        @if($isOpen)
            <div class="msgr-panel" style="position: fixed;">

                {{-- Status menu backdrop --}}
                <div x-show="showStatusMenu" x-cloak x-on:click="showStatusMenu = false" class="msgr-status-backdrop"></div>

                {{-- Status menu dropdown overlay (only in list view header) --}}
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

                {{-- ── Header ── --}}
                <div class="msgr-header">
                    @if($view === 'list')
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
                            <a href="/dm" class="msgr-icon-btn" title="Open in full view" style="text-decoration:none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            </a>
                            <button type="button" class="msgr-icon-btn" wire:click="toggleOpen" title="Collapse">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                    @elseif($view === 'conversation' && $this->activeConversation)
                        <div class="msgr-header-left">
                            <button type="button" class="msgr-icon-btn" wire:click="backToList" title="Back">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <div class="msgr-avatar-wrap">
                                <img src="{{ $this->activeConversation['other_avatar'] }}" alt="" class="msgr-avatar">
                                <span class="msgr-status-dot" x-bind:class="statusClassFor({{ $this->activeConversation['other_id'] }})"></span>
                            </div>
                            <div style="min-width:0;">
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
                            <a href="/dm" class="msgr-icon-btn" title="Open in full view" style="text-decoration:none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            </a>
                            <button type="button" class="msgr-icon-btn" wire:click="toggleOpen" title="Collapse">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                    @elseif($view === 'new_chat')
                        <div class="msgr-header-left">
                            <button type="button" class="msgr-icon-btn" wire:click="cancelNewChat" title="Back">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <div>
                                <div class="msgr-header-title">New message</div>
                                <div class="msgr-header-subtitle">Pick someone to chat with</div>
                            </div>
                        </div>
                        <div class="msgr-header-actions">
                            <a href="/dm" class="msgr-icon-btn" title="Open in full view" style="text-decoration:none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            </a>
                            <button type="button" class="msgr-icon-btn" wire:click="toggleOpen" title="Collapse">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- ── Body ── --}}
                <div class="msgr-body">

                    {{-- ─── LIST VIEW ─── --}}
                    @if($view === 'list')
                        <div class="msgr-search-bar">
                            <input type="text" class="msgr-search-input" placeholder="Search conversations…" wire:model.debounce.300ms="newChatSearch">
                        </div>
                        <div class="msgr-list">
                            @forelse($conversations as $c)
                                <div class="msgr-list-item" wire:click="openConversation({{ $c['id'] }})">
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

                    {{-- ─── CONVERSATION VIEW ─── --}}
                    @if($view === 'conversation' && $this->activeConversation)
                        {{-- OOO banner if other user is on leave --}}
                        @if(($this->activeConversation['other_status'] ?? null) === 'on_leave')
                            <div class="msgr-leave-banner">
                                <strong>{{ $this->activeConversation['other_name'] }}</strong> is on leave
                                @if(! empty($this->activeConversation['other_on_leave_until']))
                                    until {{ \Carbon\Carbon::parse($this->activeConversation['other_on_leave_until'])->format('d M Y') }}
                                @endif
                            </div>
                        @endif

                        {{-- Search bar inside conversation --}}
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

                        <div class="msgr-messages" x-ref="messagesContainer" id="msgr-messages-{{ $activeConversationId }}">
                            @if($hasMoreMessages)
                                <div class="msgr-load-more">
                                    <button type="button" wire:click="loadMoreMessages">Load older messages</button>
                                </div>
                            @endif

                            @foreach($messages as $m)
                                @if($m['is_hidden_for_me'])
                                    @continue
                                @endif
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
                                                                 x-on:click.stop="$dispatch('msgr-lightbox-open', { src: '{{ $att['preview_url'] }}', name: '{{ e($att['filename_original']) }}', download: '{{ $att['download_url'] }}' })"
                                                            >
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

                        {{-- Typing indicator placeholder (Phase 7 wires real data) --}}
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
                                 $wire.uploadMultiple('files', Array.from(dt.files), () => {}, () => {}, () => {});
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
                                 $wire.uploadMultiple('files', files, () => {}, () => {}, () => {});
                             ">
                            @if($replyToMessageId)
                                @php
                                    $replyMsg = collect($messages)->firstWhere('id', $replyToMessageId);
                                @endphp
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
                                <textarea
                                    wire:model.defer="newMessage"
                                    class="msgr-composer-textarea"
                                    placeholder="Write a message… (Enter to send)"
                                    rows="1"
                                    x-on:input="onTyping()"
                                    x-on:keydown.enter.prevent="$el.form.requestSubmit()"
                                    x-on:paste="
                                        const items = $event.clipboardData?.items;
                                        if (!items) return;
                                        const files = [];
                                        for (const item of items) {
                                            if (item.kind === 'file') { const f = item.getAsFile(); if (f) files.push(f); }
                                        }
                                        if (!files.length) return;
                                        $event.preventDefault();
                                        $wire.uploadMultiple('files', files, () => {}, () => {}, () => {});
                                    "></textarea>

                                <div class="msgr-composer-actions">
                                    <button type="button" class="msgr-icon-btn" wire:click="startJitsiMeeting" wire:loading.attr="disabled" wire:target="startJitsiMeeting" title="Start video meeting (Jitsi)">
                                        <svg wire:loading.remove wire:target="startJitsiMeeting" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <svg wire:loading wire:target="startJitsiMeeting" class="msgr-spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    </button>
                                    <label class="msgr-icon-btn" title="Attach file (images: png, jpg, max 5MB)">
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
                    @endif

                    {{-- ─── NEW CHAT PICKER VIEW ─── --}}
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
                    @endif

                </div>
            </div>
        @endif
    </div>

    {{-- Alpine widget script — Echo wiring, presence channel, sound, browser notif --}}
    @include('livewire.partials._messenger-script')

    {{-- Jitsi Meet embedded overlay (opens when user starts/joins a meet link) --}}
    @include('livewire.partials._messenger-meeting-overlay')
</div>
