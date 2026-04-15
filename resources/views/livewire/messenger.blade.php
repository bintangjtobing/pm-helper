<div>
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
        .msgr-status-dot.is-busy      { background: #ef4444; }
        .msgr-status-dot.is-in-meeting { background: #a855f7; }
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
        .msgr-lightbox {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: zoom-out;
            backdrop-filter: blur(4px);
        }
        .msgr-lightbox img {
            max-width: 90vw;
            max-height: 90vh;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.6);
            object-fit: contain;
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
                                 const input = $el.querySelector('.msgr-hidden-input');
                                 if (input) { input.files = dt.files; input.dispatchEvent(new Event('change', { bubbles: true })); }
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
                                    <label class="msgr-icon-btn" title="Attach file (images: png, jpg, max 5MB)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                        <input type="file" wire:model="files" multiple accept="image/png,image/jpeg,image/jpg,image/gif,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip" class="msgr-hidden-input">
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
</div>
