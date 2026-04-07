<div>
    <style>
        /* ─────────────────────────────────────────────────────────────────
           Messenger widget — LinkedIn-style 1-on-1 chat
           Positioned to the LEFT of the existing AI chatbot (right: 104px)
           so the two floating widgets do not collide.
           ───────────────────────────────────────────────────────────────── */

        @keyframes msgrPanelSlide {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes msgrTypingDot {
            0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
            30% { opacity: 1; transform: translateY(-3px); }
        }
        @keyframes msgrUnreadPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        .msgr-collapsed {
            position: fixed;
            bottom: 0;
            right: 104px;
            z-index: 42;
            width: 320px;
            height: 56px;
            background: #111827;
            border: 1px solid #1f2937;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 -8px 24px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
            cursor: pointer;
            user-select: none;
            transition: background 0.15s;
        }
        .msgr-collapsed:hover {
            background: #1f2937;
        }

        .msgr-panel {
            position: fixed;
            bottom: 0;
            right: 104px;
            z-index: 42;
            width: 380px;
            height: 600px;
            max-height: calc(100vh - 24px);
            background: #111827;
            border: 1px solid #1f2937;
            border-bottom: none;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 -16px 48px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: msgrPanelSlide 0.25s ease-out;
        }

        .msgr-header {
            flex-shrink: 0;
            height: 56px;
            background: #0d1117;
            border-bottom: 1px solid #1f2937;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
        }

        .msgr-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }
        .msgr-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            background: #374151;
            flex-shrink: 0;
        }
        .msgr-avatar-wrap {
            position: relative;
            flex-shrink: 0;
        }
        .msgr-online-dot {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #22c55e;
            border: 2px solid #0d1117;
        }
        .msgr-offline-dot {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: transparent;
            border: 2px solid #6b7280;
        }
        .msgr-header-title {
            font-size: 14px;
            font-weight: 600;
            color: #f3f4f6;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .msgr-header-subtitle {
            font-size: 11px;
            color: #9ca3af;
        }
        .msgr-header-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }
        .msgr-icon-btn {
            background: transparent;
            border: none;
            color: #9ca3af;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .msgr-icon-btn:hover {
            background: #1f2937;
            color: #f3f4f6;
        }
        .msgr-unread-badge {
            background: #c85a3a;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
            margin-left: 6px;
            animation: msgrUnreadPulse 2s ease-in-out infinite;
        }

        .msgr-body {
            flex: 1;
            overflow-y: auto;
            background: #111827;
            display: flex;
            flex-direction: column;
        }

        /* ── List view ── */
        .msgr-search-bar {
            padding: 12px;
            border-bottom: 1px solid #1f2937;
        }
        .msgr-search-input {
            width: 100%;
            background: #1f2937;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 8px 12px 8px 32px;
            color: #f3f4f6;
            font-size: 13px;
            outline: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%239ca3af'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-4.35-4.35M16.5 10.5a6 6 0 11-12 0 6 6 0 0112 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 10px center;
            background-size: 14px 14px;
        }
        .msgr-search-input:focus {
            border-color: #c85a3a;
        }

        .msgr-list {
            display: flex;
            flex-direction: column;
        }
        .msgr-list-empty {
            padding: 40px 20px;
            text-align: center;
            color: #6b7280;
            font-size: 13px;
        }
        .msgr-list-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            cursor: pointer;
            border-bottom: 1px solid #1f2937;
            transition: background 0.15s;
        }
        .msgr-list-item:hover {
            background: #1f2937;
        }
        .msgr-list-item-content {
            flex: 1;
            min-width: 0;
        }
        .msgr-list-item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .msgr-list-name {
            font-size: 14px;
            font-weight: 600;
            color: #f3f4f6;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .msgr-list-name-unread {
            color: #ffffff;
        }
        .msgr-list-time {
            font-size: 11px;
            color: #9ca3af;
            flex-shrink: 0;
        }
        .msgr-list-preview {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .msgr-list-preview-unread {
            color: #f3f4f6;
            font-weight: 500;
        }
        .msgr-list-attachment-icon {
            display: inline-block;
            margin-right: 4px;
            opacity: 0.7;
        }
        .msgr-list-unread-pill {
            background: #c85a3a;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            border-radius: 10px;
            padding: 1px 6px;
            min-width: 18px;
            text-align: center;
        }

        /* ── Conversation view ── */
        .msgr-messages {
            flex: 1;
            overflow-y: auto;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .msgr-load-more {
            text-align: center;
            padding: 8px;
        }
        .msgr-load-more button {
            background: transparent;
            border: 1px solid #1f2937;
            color: #9ca3af;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 12px;
            cursor: pointer;
        }
        .msgr-load-more button:hover {
            background: #1f2937;
        }

        .msgr-msg-row {
            display: flex;
            gap: 8px;
            position: relative;
        }
        .msgr-msg-row-self {
            justify-content: flex-end;
        }
        .msgr-msg-bubble {
            max-width: 75%;
            display: flex;
            flex-direction: column;
            gap: 2px;
            position: relative;
        }
        .msgr-msg-sender-name {
            font-size: 11px;
            color: #9ca3af;
            margin-left: 8px;
            margin-bottom: 2px;
        }
        .msgr-msg-reply-quote {
            background: rgba(255,255,255,0.05);
            border-left: 3px solid #c85a3a;
            padding: 6px 10px;
            border-radius: 6px;
            margin-bottom: 4px;
            font-size: 11px;
            color: #9ca3af;
        }
        .msgr-msg-reply-quote-name {
            font-weight: 600;
            color: #c85a3a;
            display: block;
            margin-bottom: 2px;
        }
        .msgr-msg-content {
            padding: 8px 12px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.45;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        .msgr-msg-self .msgr-msg-content {
            background: #c85a3a;
            color: #fff;
            border-bottom-right-radius: 4px;
        }
        .msgr-msg-other .msgr-msg-content {
            background: #374151;
            color: #f3f4f6;
            border-bottom-left-radius: 4px;
        }
        .msgr-msg-deleted {
            font-style: italic;
            opacity: 0.6;
        }
        .msgr-msg-edited-mark {
            font-size: 10px;
            color: #9ca3af;
            margin-left: 4px;
            font-style: italic;
        }
        .msgr-msg-meta {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            color: #9ca3af;
            margin-top: 2px;
        }
        .msgr-msg-self .msgr-msg-meta {
            justify-content: flex-end;
        }
        .msgr-tick {
            color: #9ca3af;
        }
        .msgr-tick-read {
            color: #60a5fa;
        }

        /* Hover dropdown menu */
        .msgr-msg-actions {
            position: absolute;
            top: 0;
            right: 100%;
            margin-right: 4px;
            opacity: 0;
            transition: opacity 0.15s;
        }
        .msgr-msg-row-self .msgr-msg-actions {
            right: 100%;
            left: auto;
        }
        .msgr-msg-row-other .msgr-msg-actions {
            right: auto;
            left: 100%;
            margin-right: 0;
            margin-left: 4px;
        }
        .msgr-msg-row:hover .msgr-msg-actions {
            opacity: 1;
        }
        .msgr-msg-action-btn {
            background: #1f2937;
            border: 1px solid #374151;
            color: #9ca3af;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-bottom: 4px;
        }
        .msgr-msg-action-btn:hover {
            background: #374151;
            color: #f3f4f6;
        }

        /* Reactions */
        .msgr-reactions {
            display: flex;
            gap: 4px;
            margin-top: 4px;
            flex-wrap: wrap;
        }
        .msgr-reaction-chip {
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
        }
        .msgr-reaction-chip-active {
            background: rgba(200,90,58,0.2);
            border-color: #c85a3a;
        }
        .msgr-reaction-chip-count {
            font-size: 10px;
            color: #9ca3af;
            font-weight: 600;
        }
        .msgr-emoji-picker {
            position: absolute;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 20px;
            padding: 4px 8px;
            display: flex;
            gap: 4px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            z-index: 5;
        }
        .msgr-emoji-picker button {
            background: transparent;
            border: none;
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
            border-radius: 50%;
            transition: transform 0.1s, background 0.1s;
        }
        .msgr-emoji-picker button:hover {
            background: #374151;
            transform: scale(1.2);
        }

        /* Attachments */
        .msgr-attachments {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 6px;
        }
        .msgr-attachment-image {
            max-width: 240px;
            max-height: 240px;
            border-radius: 8px;
            cursor: pointer;
            display: block;
        }
        .msgr-attachment-file {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: inherit;
            max-width: 240px;
        }
        .msgr-attachment-file-info {
            flex: 1;
            min-width: 0;
        }
        .msgr-attachment-file-name {
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .msgr-attachment-file-size {
            font-size: 10px;
            opacity: 0.7;
        }

        /* Composer */
        .msgr-composer {
            flex-shrink: 0;
            border-top: 1px solid #1f2937;
            background: #0d1117;
            padding: 10px 12px;
        }
        .msgr-composer-reply-bar {
            background: rgba(200,90,58,0.1);
            border-left: 3px solid #c85a3a;
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            color: #d1d5db;
        }
        .msgr-composer-files {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 8px;
        }
        .msgr-composer-file-chip {
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 11px;
            color: #d1d5db;
            display: flex;
            align-items: center;
            gap: 6px;
            max-width: 200px;
        }
        .msgr-composer-file-chip span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .msgr-composer-row {
            display: flex;
            align-items: flex-end;
            gap: 8px;
        }
        .msgr-composer-textarea {
            flex: 1;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 18px;
            padding: 8px 14px;
            color: #f3f4f6;
            font-size: 13px;
            outline: none;
            resize: none;
            min-height: 36px;
            max-height: 120px;
            font-family: inherit;
            line-height: 1.4;
        }
        .msgr-composer-textarea:focus {
            border-color: #c85a3a;
        }
        .msgr-composer-actions {
            display: flex;
            gap: 4px;
        }
        .msgr-send-btn {
            background: #c85a3a;
            border: none;
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s;
        }
        .msgr-send-btn:hover {
            background: #a0492f;
        }
        .msgr-send-btn:disabled {
            background: #38434f;
            cursor: not-allowed;
        }

        /* Typing indicator */
        .msgr-typing {
            padding: 6px 12px;
            font-size: 11px;
            color: #9ca3af;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .msgr-typing-dot {
            width: 5px;
            height: 5px;
            background: #9ca3af;
            border-radius: 50%;
            display: inline-block;
            animation: msgrTypingDot 1.4s infinite;
        }
        .msgr-typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .msgr-typing-dot:nth-child(3) { animation-delay: 0.4s; }

        /* New chat picker */
        .msgr-picker-list {
            display: flex;
            flex-direction: column;
        }
        .msgr-picker-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #1f2937;
        }
        .msgr-picker-item:hover {
            background: #1f2937;
        }
        .msgr-picker-name {
            font-size: 13px;
            font-weight: 600;
            color: #f3f4f6;
        }
        .msgr-picker-username {
            font-size: 11px;
            color: #9ca3af;
        }

        /* Search results panel */
        .msgr-search-results {
            border-bottom: 1px solid #1f2937;
        }
        .msgr-search-result {
            padding: 8px 14px;
            border-bottom: 1px solid #1f2937;
            cursor: pointer;
        }
        .msgr-search-result:hover {
            background: #1f2937;
        }
        .msgr-search-result-meta {
            font-size: 10px;
            color: #9ca3af;
            margin-bottom: 2px;
        }
        .msgr-search-result-body {
            font-size: 12px;
            color: #d1d5db;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Error inline */
        .msgr-error {
            color: #f87171;
            font-size: 11px;
            margin-top: 4px;
        }

        /* Hidden file input */
        .msgr-hidden-input {
            display: none;
        }

        /* Mobile fullscreen */
        @media (max-width: 640px) {
            .msgr-collapsed {
                right: 8px;
                left: 8px;
                width: auto;
                bottom: 0;
            }
            .msgr-panel {
                right: 0;
                left: 0;
                width: auto;
                top: 0;
                bottom: 0;
                height: auto;
                max-height: none;
                border-radius: 0;
                border: none;
            }
        }
    </style>

    <div x-data="messengerWidget()" x-init="init()">

        {{-- ────────────────────────────────────────────────────────────────
             COLLAPSED STATE — bottom bar acting as launcher
             ──────────────────────────────────────────────────────────────── --}}
        @if(! $isOpen)
            <div class="msgr-collapsed" wire:click="toggleOpen">
                <div class="msgr-header-left">
                    <div class="msgr-avatar-wrap">
                        <img src="{{ auth()->user()->avatar_url }}" alt="me" class="msgr-avatar">
                        <span class="msgr-online-dot"></span>
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
            <div class="msgr-panel">

                {{-- ── Header ── --}}
                <div class="msgr-header">
                    @if($view === 'list')
                        <div class="msgr-header-left">
                            <div class="msgr-avatar-wrap">
                                <img src="{{ auth()->user()->avatar_url }}" alt="me" class="msgr-avatar">
                                <span class="msgr-online-dot"></span>
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
                            <button type="button" class="msgr-icon-btn" wire:click="openNewChatPicker" title="New message">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
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
                                <span class="msgr-offline-dot" x-bind:class="onlineUserIds.includes({{ $this->activeConversation['other_id'] }}) ? 'msgr-online-dot' : 'msgr-offline-dot'"></span>
                            </div>
                            <div style="min-width:0;">
                                <div class="msgr-header-title">{{ $this->activeConversation['other_name'] }}</div>
                                <div class="msgr-header-subtitle"
                                     x-text="onlineUserIds.includes({{ $this->activeConversation['other_id'] }}) ? 'Online' : 'Offline'"></div>
                            </div>
                        </div>
                        <div class="msgr-header-actions">
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
                                        <span x-bind:class="onlineUserIds.includes({{ $c['other_id'] }}) ? 'msgr-online-dot' : 'msgr-offline-dot'"></span>
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
                                                    <button type="button" wire:click="saveEdit" style="background:#c85a3a;border:none;color:#fff;padding:3px 10px;border-radius:6px;font-size:11px;cursor:pointer;">Save</button>
                                                </div>
                                                @error('editingBody') <div class="msgr-error">{{ $message }}</div> @enderror
                                            </div>
                                        @else
                                            @if($m['is_deleted_for_all'])
                                                <div class="msgr-msg-content msgr-msg-deleted">Message deleted</div>
                                            @elseif($m['body'])
                                                <div class="msgr-msg-content">{{ $m['body'] }}@if($m['edited_at'])<span class="msgr-msg-edited-mark">(edited)</span>@endif</div>
                                            @endif

                                            @if(! empty($m['attachments']) && ! $m['is_deleted_for_all'])
                                                <div class="msgr-attachments">
                                                    @foreach($m['attachments'] as $att)
                                                        @if($att['is_image'])
                                                            <a href="{{ $att['download_url'] }}" target="_blank">
                                                                <img src="{{ $att['preview_url'] }}" alt="{{ $att['filename_original'] }}" class="msgr-attachment-image" loading="lazy">
                                                            </a>
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
                                                <span class="msgr-tick {{ $readByOther ? 'msgr-tick-read' : '' }}" title="{{ $readByOther ? 'Read' : 'Sent' }}">
                                                    @if($readByOther) ✓✓ @else ✓ @endif
                                                </span>
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
                        <div class="msgr-composer">
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
                                            <span>{{ $file->getClientOriginalName() }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @error('files') <div class="msgr-error">{{ $message }}</div> @enderror
                            @error('newMessage') <div class="msgr-error">{{ $message }}</div> @enderror

                            <div class="msgr-composer-row">
                                <textarea
                                    wire:model.defer="newMessage"
                                    class="msgr-composer-textarea"
                                    placeholder="Write a message… (Enter to send)"
                                    rows="1"
                                    x-on:input="onTyping()"
                                    x-on:keydown.enter.prevent="$wire.call('sendMessage')"></textarea>

                                <div class="msgr-composer-actions">
                                    <label class="msgr-icon-btn" title="Attach file">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <input type="file" wire:model="files" multiple class="msgr-hidden-input">
                                    </label>
                                    <button type="button" class="msgr-send-btn" wire:click="sendMessage" title="Send">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    </button>
                                </div>
                            </div>
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
    <script>
        function messengerWidget() {
            return {
                // ── Reactive state ──
                onlineUserIds: [],
                isOtherTyping: false,
                typingTimeoutId: null,
                lastWhisperAt: 0,

                // ── Internal state (not exposed to Alpine reactivity) ──
                subscribedChannels: {},     // { conversationId: channel }
                presenceChannel: null,
                notificationAudio: null,
                notificationPermissionAsked: false,
                currentUserId: {{ (int) auth()->id() }},

                init() {
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
                            })
                            .joining((user) => {
                                const id = parseInt(user.id, 10);
                                if (! this.onlineUserIds.includes(id)) {
                                    this.onlineUserIds.push(id);
                                }
                            })
                            .leaving((user) => {
                                const id = parseInt(user.id, 10);
                                this.onlineUserIds = this.onlineUserIds.filter(x => x !== id);
                            });
                    } catch (e) {
                        console.warn('[messenger] presence channel failed', e);
                    }
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
</div>
