    <style>
        /* ─────────────────────────────────────────────────────────────────
           Messenger widget — LinkedIn-style 1-on-1 chat
           Positioned to the LEFT of the existing AI chatbot (right: 104px)
           so the two floating widgets do not collide.
           ───────────────────────────────────────────────────────────────── */

        [x-cloak] { display: none !important; }

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
            overflow: visible;
            animation: msgrPanelSlide 0.25s ease-out;
        }

        .msgr-header {
            flex-shrink: 0;
            height: 56px;
            background: #0d1117;
            border-bottom: 1px solid #1f2937;
            border-radius: 12px 12px 0 0;
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
        /* Status dot — single class, modifier sets color */
        .msgr-status-dot {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: #6b7280;
            border: 2px solid #0d1117;
            box-sizing: content-box;
        }
        /* Inline (non-positioned) variant for menu items */
        .msgr-status-dot-inline {
            position: relative;
            display: inline-block;
            bottom: auto;
            right: auto;
            border-color: transparent;
            margin-right: 8px;
            vertical-align: middle;
        }
        .msgr-status-dot.is-online    { background: #22c55e; }
        .msgr-status-dot.is-busy      { background: #dc2626; }
        .msgr-status-dot.is-in-meeting { background: #ef4444; }
        .msgr-status-dot.is-lunch-break { background: #f59e0b; }
        /* Pulsing animation on in_meeting dot to visually indicate an active call */
        .msgr-status-dot.is-in-meeting {
            animation: msgrMeetDotPulse 1.6s ease-in-out infinite;
        }
        @keyframes msgrMeetDotPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.6); }
            50%      { box-shadow: 0 0 0 4px rgba(239, 68, 68, 0); }
        }
        .msgr-status-dot.is-on-leave  { background: #6b7280; border-color: #fbbf24; }

        /* ── Status menu (dropdown overlay) ── */
        .msgr-status-backdrop {
            position: fixed;
            inset: 0;
            z-index: 49;
        }
        .msgr-status-menu {
            position: absolute;
            top: 56px;
            left: 0;
            right: 0;
            background: #0f172a;
            border: 1px solid #374151;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            padding: 6px 0;
            z-index: 50;
        }
        .msgr-status-menu-header {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 14px 4px;
        }
        .msgr-status-option {
            display: flex;
            align-items: center;
            width: 100%;
            background: transparent;
            border: none;
            padding: 8px 14px;
            color: #e5e7eb;
            font-size: 13px;
            text-align: left;
            cursor: pointer;
        }
        .msgr-status-option:hover {
            background: #374151;
        }
        .msgr-status-option-meta {
            font-size: 10px;
            color: #9ca3af;
            margin-left: 6px;
        }
        .msgr-status-leave-form {
            padding: 8px 14px;
            background: #111827;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .msgr-status-leave-form input[type="date"] {
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 6px;
            padding: 5px 8px;
            color: #e5e7eb;
            font-size: 12px;
            color-scheme: dark;
        }
        .msgr-status-leave-form button {
            background: #3b82f6;
            border: none;
            border-radius: 6px;
            padding: 5px 10px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .msgr-status-divider {
            border: none;
            border-top: 1px solid #374151;
            margin: 4px 0;
        }
        .msgr-status-message-input {
            width: calc(100% - 20px);
            margin: 6px 10px;
            background: #111827;
            border: 1px solid #374151;
            border-radius: 6px;
            padding: 6px 10px;
            color: #e5e7eb;
            font-size: 12px;
            outline: none;
        }
        .msgr-status-message-input:focus {
            border-color: #3b82f6;
        }

        /* OOO warning banner in conversation header */
        .msgr-leave-banner {
            background: rgba(251, 191, 36, 0.1);
            border-bottom: 1px solid rgba(251, 191, 36, 0.3);
            color: #fbbf24;
            padding: 8px 14px;
            font-size: 12px;
            text-align: center;
        }
        .msgr-leave-banner strong {
            color: #fde68a;
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
            background: #3b82f6;
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
            overflow-x: hidden;
            background: #111827;
            display: flex;
            flex-direction: column;
            min-height: 0;
            border-radius: 0 0 12px 12px;
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
            border-color: #3b82f6;
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
            background: #3b82f6;
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
            overflow-x: hidden;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-height: 0;
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
            min-width: 0;
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
            border-left: 3px solid #3b82f6;
            padding: 6px 10px;
            border-radius: 6px;
            margin-bottom: 4px;
            font-size: 11px;
            color: #9ca3af;
        }
        .msgr-msg-reply-quote-name {
            font-weight: 600;
            color: #3b82f6;
            display: block;
            margin-bottom: 2px;
        }
        .msgr-msg-content {
            padding: 8px 12px;
            border-radius: 14px;
            font-size: 13px;
            line-height: 1.45;
            word-wrap: break-word;
            overflow-wrap: anywhere;
            word-break: break-word;
            white-space: pre-wrap;
            min-width: 0;
        }
        .msgr-msg-self .msgr-msg-content {
            background: #3b82f6;
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
            display: inline-block;
            color: #9ca3af;
            margin-left: 2px;
            vertical-align: middle;
        }
        .msgr-tick-read {
            color: #60a5fa;
        }

        /* Hover action toolbar — Slack-style overlay above bubble */
        .msgr-msg-actions {
            position: absolute;
            top: -10px;
            display: none;
            flex-direction: row;
            gap: 2px;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 14px;
            padding: 2px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.4);
            z-index: 5;
        }
        .msgr-msg-row-self .msgr-msg-actions {
            right: 8px;
        }
        .msgr-msg-row-other .msgr-msg-actions {
            left: 8px;
        }
        .msgr-msg-row:hover .msgr-msg-actions {
            display: flex;
        }
        .msgr-msg-action-btn {
            background: transparent;
            border: none;
            color: #9ca3af;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
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
            background: rgba(59,130,246,0.2);
            border-color: #3b82f6;
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
            max-width: 380px;
            max-height: 380px;
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
        .msgr-lightbox {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.85);
            display: flex;
            align-items: safe center;
            justify-content: safe center;
            cursor: zoom-out;
            backdrop-filter: blur(4px);
            overflow: auto;
            padding: 24px;
        }
        .msgr-lightbox img {
            display: block;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
        }
        .msgr-lightbox-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .msgr-lightbox-close:hover {
            background: rgba(255,255,255,0.3);
        }
        .msgr-lightbox-download {
            position: absolute;
            bottom: 16px;
            right: 16px;
            padding: 6px 14px;
            border-radius: 8px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .msgr-lightbox-download:hover {
            background: rgba(255,255,255,0.3);
        }

        /* Composer */
        .msgr-composer {
            flex-shrink: 0;
            border-top: 1px solid #1f2937;
            background: #0d1117;
            padding: 10px 12px;
            transition: background 0.15s, border-color 0.15s;
        }
        .msgr-composer-dragover {
            background: rgba(59, 130, 246, 0.08);
            border-top-color: #3b82f6;
        }
        .msgr-composer-reply-bar {
            background: rgba(59,130,246,0.1);
            border-left: 3px solid #3b82f6;
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
            border-color: #3b82f6;
        }
        .msgr-composer-actions {
            display: flex;
            gap: 4px;
        }
        .msgr-send-btn {
            background: #3b82f6;
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
            flex-shrink: 0;
        }
        .msgr-send-btn:hover {
            background: #2563eb;
        }
        .msgr-send-btn:disabled {
            background: #374151;
            cursor: not-allowed;
        }
        @keyframes msgrSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .msgr-spin {
            animation: msgrSpin 0.8s linear infinite;
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

        /* ── Ticket badge in chat messages ──────────────────────────── */
        .msgr-ticket-badge {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            padding: 1px 7px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            position: relative;
            transition: background 0.15s;
            vertical-align: baseline;
            line-height: 1.4;
        }
        .msgr-ticket-badge:hover {
            background: rgba(59, 130, 246, 0.35);
            color: #93bbfc;
            text-decoration: none;
        }
        .msgr-msg-self .msgr-ticket-badge {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .msgr-msg-self .msgr-ticket-badge:hover {
            background: rgba(255, 255, 255, 0.35);
            color: #fff;
        }
        .msgr-ticket-badge-icon {
            font-weight: 700;
            opacity: 0.7;
            font-size: 11px;
        }
        /* Tooltip on hover */
        .msgr-ticket-badge::after {
            content: attr(data-ticket-title) "\A" "Status: " attr(data-ticket-status) "\A" "Assignee: " attr(data-ticket-assignee);
            white-space: pre-wrap;
            position: absolute;
            bottom: calc(100% + 6px);
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: #e2e8f0;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 400;
            line-height: 1.5;
            min-width: 180px;
            max-width: 280px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s;
            z-index: 100;
        }
        .msgr-ticket-badge::before {
            content: '';
            position: absolute;
            bottom: calc(100% + 2px);
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #1e293b;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s;
            z-index: 100;
        }
        .msgr-ticket-badge:hover::after,
        .msgr-ticket-badge:hover::before {
            opacity: 1;
        }
        /* Ticket not found in DB — still clickable, slightly dimmer */
        .msgr-ticket-badge-unknown {
            opacity: 0.75;
        }
        .msgr-ticket-badge-unknown::after,
        .msgr-ticket-badge-unknown::before {
            display: none;
        }

        /* ── Auto-linked URLs ─────────────────────────────────────── */
        .msgr-auto-link {
            color: #60a5fa;
            text-decoration: underline;
            text-decoration-color: rgba(96, 165, 250, 0.5);
            text-underline-offset: 2px;
            word-break: break-all;
            overflow-wrap: anywhere;
            transition: color 0.15s, text-decoration-color 0.15s;
        }
        .msgr-auto-link:hover {
            color: #93c5fd;
            text-decoration-color: rgba(147, 197, 253, 0.8);
        }
        .msgr-msg-self .msgr-auto-link {
            color: #dbeafe;
            text-decoration-color: rgba(219, 234, 254, 0.6);
        }
        .msgr-msg-self .msgr-auto-link:hover {
            color: #fff;
            text-decoration-color: rgba(255, 255, 255, 0.9);
        }

        /* ── Jitsi meeting join pill ──────────────────────────────── */
        .msgr-meet-join-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 6px;
            padding: 6px 12px;
            background: #10b981;
            color: #fff !important;
            text-decoration: none !important;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.15s, transform 0.15s;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.25);
        }
        .msgr-meet-join-pill:hover {
            background: #059669;
            color: #fff !important;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.35);
        }
        .msgr-meet-join-pill svg {
            flex-shrink: 0;
        }
        .msgr-msg-self .msgr-meet-join-pill {
            background: rgba(255, 255, 255, 0.9);
            color: #047857 !important;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }
        .msgr-msg-self .msgr-meet-join-pill:hover {
            background: #fff;
            color: #065f46 !important;
        }

        /* ── Summarize-transcript button (AI / GPT-4o) ────────────── */
        .msgr-summarize-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 6px;
            padding: 5px 10px;
            background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: filter 0.15s, transform 0.15s;
            box-shadow: 0 2px 6px rgba(99, 102, 241, 0.3);
        }
        .msgr-summarize-btn:hover:not(:disabled) {
            filter: brightness(1.1);
            transform: translateY(-1px);
        }
        .msgr-summarize-btn:disabled {
            opacity: 0.7;
            cursor: wait;
        }
        .msgr-summarize-btn svg { flex-shrink: 0; }

        /* ── Link preview card ────────────────────────────────────── */
        .msgr-link-preview {
            display: block;
            margin-top: 6px;
            border-radius: 10px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.08);
            text-decoration: none;
            color: inherit;
            transition: background 0.15s;
            max-width: 320px;
        }
        .msgr-link-preview:hover {
            background: rgba(0, 0, 0, 0.3);
            text-decoration: none;
            color: inherit;
        }
        .msgr-link-preview-image {
            width: 100%;
            height: 140px;
            object-fit: cover;
            display: block;
        }
        .msgr-link-preview-body {
            padding: 8px 10px;
        }
        .msgr-link-preview-domain {
            font-size: 10px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .msgr-link-preview-title {
            font-size: 12px;
            font-weight: 600;
            color: #e2e8f0;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .msgr-link-preview-desc {
            font-size: 11px;
            color: #9ca3af;
            line-height: 1.35;
            margin-top: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .msgr-msg-self .msgr-link-preview {
            background: rgba(0, 0, 0, 0.15);
            border-color: rgba(255, 255, 255, 0.12);
        }
        .msgr-msg-self .msgr-link-preview:hover {
            background: rgba(0, 0, 0, 0.25);
        }
        .msgr-msg-self .msgr-link-preview-domain {
            color: rgba(255, 255, 255, 0.6);
        }
        .msgr-msg-self .msgr-link-preview-title {
            color: #fff;
        }
        .msgr-msg-self .msgr-link-preview-desc {
            color: rgba(255, 255, 255, 0.7);
        }
    </style>
