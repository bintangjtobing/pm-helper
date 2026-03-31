<div>
    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes chatBotBounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        @keyframes chatBotPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            50% { box-shadow: 0 0 0 12px rgba(59, 130, 246, 0); }
        }
        @keyframes chatPanelSlide {
            from { opacity: 0; transform: translateY(20px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes typingDot {
            0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
            30% { opacity: 1; transform: translateY(-4px); }
        }
        @keyframes chatBotWiggle {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-3deg); }
            75% { transform: rotate(3deg); }
        }
        .chat-bot-button {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 40;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            background: transparent;
            padding: 0;
            animation: chatBotBounce 3s ease-in-out infinite, chatBotPulse 3s ease-in-out infinite;
            transition: transform 0.2s;
        }
        .chat-bot-button:hover {
            animation: chatBotWiggle 0.5s ease-in-out;
            transform: scale(1.1);
        }
        .chat-bot-button img {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
        }
        .chat-panel {
            position: fixed;
            bottom: 96px;
            right: 24px;
            z-index: 41;
            width: 400px;
            max-width: calc(100vw - 32px);
            height: 560px;
            max-height: calc(100vh - 120px);
            background: #111827;
            border-radius: 16px;
            border: 1px solid #1f2937;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: chatPanelSlide 0.3s ease-out;
        }
        .chat-header {
            background: #0d1117;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #1f2937;
            flex-shrink: 0;
        }
        .chat-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .chat-header-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        .chat-header-title {
            font-size: 14px;
            font-weight: 600;
            color: #f3f4f6;
        }
        .chat-header-subtitle {
            font-size: 11px;
            color: #6b7280;
        }
        .chat-header-actions {
            display: flex;
            gap: 4px;
        }
        .chat-header-btn {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            font-size: 12px;
            transition: all 0.15s;
        }
        .chat-header-btn:hover {
            background: #1f2937;
            color: #f3f4f6;
        }
        .chat-lang-picker {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            padding: 40px 24px;
        }
        .chat-lang-picker img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            animation: chatBotBounce 2s ease-in-out infinite;
        }
        .chat-lang-title {
            font-size: 16px;
            font-weight: 600;
            color: #f3f4f6;
            text-align: center;
        }
        .chat-lang-subtitle {
            font-size: 13px;
            color: #9ca3af;
            text-align: center;
            margin-top: -12px;
        }
        .chat-lang-buttons {
            display: flex;
            gap: 12px;
            width: 100%;
        }
        .chat-lang-btn {
            flex: 1;
            padding: 16px 12px;
            border-radius: 12px;
            border: 2px solid #374151;
            background: #1f2937;
            color: #f3f4f6;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            font-size: 14px;
            font-weight: 500;
        }
        .chat-lang-btn:hover {
            border-color: #3b82f6;
            background: #1e3a5f;
        }
        .chat-lang-btn span {
            display: block;
            font-size: 28px;
            margin-bottom: 6px;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            scrollbar-width: thin;
            scrollbar-color: #374151 transparent;
        }
        .chat-messages::-webkit-scrollbar {
            width: 4px;
        }
        .chat-messages::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 4px;
        }
        .chat-msg {
            display: flex;
            gap: 8px;
            max-width: 85%;
        }
        .chat-msg-user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }
        .chat-msg-assistant {
            align-self: flex-start;
        }
        .chat-msg-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            flex-shrink: 0;
            object-fit: cover;
        }
        .chat-msg-bubble {
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.5;
            word-break: break-word;
        }
        .chat-msg-user .chat-msg-bubble {
            background: #1d4ed8;
            color: #ffffff;
            border-bottom-right-radius: 4px;
        }
        .chat-msg-assistant .chat-msg-bubble {
            background: #1f2937;
            color: #e5e7eb;
            border-bottom-left-radius: 4px;
            border: 1px solid #374151;
        }
        .chat-msg-time {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
            text-align: right;
        }
        .chat-msg-assistant .chat-msg-time {
            text-align: left;
        }
        .chat-msg-bubble p { margin: 0 0 8px 0; }
        .chat-msg-bubble p:last-child { margin-bottom: 0; }
        .chat-msg-bubble strong { font-weight: 600; color: #ffffff; }
        .chat-msg-assistant .chat-msg-bubble strong { color: #f3f4f6; }
        .chat-msg-bubble em { font-style: italic; }
        .chat-msg-bubble ul, .chat-msg-bubble ol {
            margin: 6px 0;
            padding-left: 20px;
        }
        .chat-msg-bubble li { margin-bottom: 4px; }
        .chat-msg-bubble ol { list-style-type: decimal; }
        .chat-msg-bubble ul { list-style-type: disc; }
        .chat-msg-bubble code {
            background: rgba(0,0,0,0.3);
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 12px;
            font-family: monospace;
        }
        .chat-msg-bubble a {
            color: #60a5fa;
            text-decoration: underline;
        }
        .chat-msg-bubble h1, .chat-msg-bubble h2, .chat-msg-bubble h3,
        .chat-msg-bubble h4, .chat-msg-bubble h5, .chat-msg-bubble h6 {
            font-weight: 600;
            margin: 8px 0 4px 0;
            color: #f3f4f6;
        }
        .chat-msg-bubble h1 { font-size: 16px; }
        .chat-msg-bubble h2 { font-size: 15px; }
        .chat-msg-bubble h3 { font-size: 14px; }
        .chat-msg-bubble blockquote {
            border-left: 3px solid #4b5563;
            padding-left: 10px;
            margin: 6px 0;
            color: #9ca3af;
        }
        .chat-msg-meta {
            margin-top: 8px;
            padding: 8px 10px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 8px;
            font-size: 11px;
            color: #93c5fd;
        }
        .chat-typing {
            display: flex;
            gap: 4px;
            padding: 12px 14px;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 12px;
            border-bottom-left-radius: 4px;
            align-self: flex-start;
            margin-left: 36px;
        }
        .chat-typing-dot {
            width: 6px;
            height: 6px;
            background: #6b7280;
            border-radius: 50%;
        }
        .chat-typing-dot:nth-child(1) { animation: typingDot 1.4s infinite 0s; }
        .chat-typing-dot:nth-child(2) { animation: typingDot 1.4s infinite 0.2s; }
        .chat-typing-dot:nth-child(3) { animation: typingDot 1.4s infinite 0.4s; }
        .chat-input-area {
            padding: 12px 16px;
            border-top: 1px solid #1f2937;
            background: #0d1117;
            display: flex;
            gap: 8px;
            align-items: flex-end;
            flex-shrink: 0;
        }
        .chat-input {
            flex: 1;
            background: #1f2937;
            border: 1px solid #374151;
            border-radius: 10px;
            padding: 10px 14px;
            color: #f3f4f6;
            font-size: 13px;
            outline: none;
            resize: none;
            font-family: inherit;
            line-height: 1.4;
            max-height: 80px;
            transition: border-color 0.15s;
        }
        .chat-input:focus {
            border-color: #3b82f6;
        }
        .chat-input::placeholder {
            color: #6b7280;
        }
        .chat-send-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: none;
            background: #2563eb;
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background 0.15s;
        }
        .chat-send-btn:hover {
            background: #1d4ed8;
        }
        .chat-send-btn:disabled {
            background: #374151;
            cursor: not-allowed;
        }
        @media (max-width: 480px) {
            .chat-panel {
                width: calc(100vw - 16px);
                right: 8px;
                bottom: 88px;
                height: calc(100vh - 100px);
            }
            .chat-bot-button {
                bottom: 16px;
                right: 16px;
                width: 56px;
                height: 56px;
            }
            .chat-bot-button img {
                width: 56px;
                height: 56px;
            }
        }
    </style>

    {{-- Floating Bot Button --}}
    <button class="chat-bot-button" wire:click="toggleChat" title="Chat with PM Assistant">
        <img src="{{ asset('images/chat-bot.png') }}" alt="PM Assistant">
    </button>

    {{-- Chat Panel --}}
    @if($isOpen)
    <div class="chat-panel">
        {{-- Header --}}
        <div class="chat-header">
            <div class="chat-header-left">
                <img src="{{ asset('images/chat-bot.png') }}" alt="Bot" class="chat-header-avatar">
                <div>
                    <div class="chat-header-title">PM Assistant</div>
                    <div class="chat-header-subtitle">
                        @if($language === 'id') Bahasa Indonesia
                        @elseif($language === 'en') English
                        @else Pilih bahasa
                        @endif
                    </div>
                </div>
            </div>
            <div class="chat-header-actions">
                @if($conversationId)
                <button class="chat-header-btn" wire:click="newConversation" title="New conversation">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </button>
                @endif
                <button class="chat-header-btn" wire:click="toggleChat" title="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        @if($showLanguagePicker)
        {{-- Language Picker --}}
        <div class="chat-lang-picker">
            <img src="{{ asset('images/chat-bot.png') }}" alt="Bot">
            <div class="chat-lang-title">Pilih Bahasa / Select Language</div>
            <div class="chat-lang-subtitle">Choose your preferred language for this conversation</div>
            <div class="chat-lang-buttons">
                <button class="chat-lang-btn" wire:click="selectLanguage('id')">
                    <span>&#127470;&#127465;</span>
                    Bahasa Indonesia
                </button>
                <button class="chat-lang-btn" wire:click="selectLanguage('en')">
                    <span>&#127468;&#127463;</span>
                    English
                </button>
            </div>
        </div>
        @else
        {{-- Messages --}}
        <div class="chat-messages"
             id="chatMessagesContainer"
             x-data
             x-init="
                 $nextTick(() => $el.scrollTop = $el.scrollHeight);
                 const obs = new MutationObserver(() => $el.scrollTop = $el.scrollHeight);
                 obs.observe($el, { childList: true, subtree: true });
             ">
            @foreach($chatMessages as $msg)
                @if($msg['role'] === 'system') @continue @endif
                <div class="chat-msg chat-msg-{{ $msg['role'] }}">
                    @if($msg['role'] === 'assistant')
                        <img src="{{ asset('images/chat-bot.png') }}" alt="Bot" class="chat-msg-avatar">
                    @endif
                    <div>
                        <div class="chat-msg-bubble">
                            {!! \Illuminate\Support\Str::markdown($msg['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                            @if(!empty($msg['metadata']))
                                <div class="chat-msg-meta">
                                    @if(($msg['metadata']['type'] ?? '') === 'feedback_created')
                                        {{ $language === 'id' ? 'Feedback dibuat' : 'Feedback created' }}
                                        - #{{ $msg['metadata']['feedback_id'] ?? '' }}
                                        ({{ $language === 'id' ? 'menunggu persetujuan PM' : 'pending PM approval' }})
                                    @elseif(($msg['metadata']['type'] ?? '') === 'feedback_linked')
                                        {{ $language === 'id' ? 'Feedback ditautkan ke tiket' : 'Feedback linked to ticket' }}
                                        {{ $msg['metadata']['linked_ticket'] ?? '' }}
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="chat-msg-time">{{ $msg['time'] }}</div>
                    </div>
                </div>
            @endforeach

            @if($isLoading)
            <div style="display:flex;align-items:flex-start;gap:8px;">
                <img src="{{ asset('images/chat-bot.png') }}" alt="Bot" class="chat-msg-avatar">
                <div class="chat-typing">
                    <div class="chat-typing-dot"></div>
                    <div class="chat-typing-dot"></div>
                    <div class="chat-typing-dot"></div>
                </div>
            </div>
            @endif
        </div>

        {{-- Input Area --}}
        <div class="chat-input-area">
            <input type="text"
                   class="chat-input"
                   wire:model.defer="message"
                   wire:keydown.enter="sendMessage"
                   wire:loading.attr="disabled"
                   wire:target="sendMessage"
                   placeholder="{{ $language === 'id' ? 'Ketik pesan...' : 'Type a message...' }}"
                   {{ $isLoading ? 'disabled' : '' }}
                   autocomplete="off">
            <button class="chat-send-btn"
                    wire:click="sendMessage"
                    wire:loading.attr="disabled"
                    wire:target="sendMessage"
                    {{ $isLoading ? 'disabled' : '' }}>
                <span wire:loading.remove wire:target="sendMessage">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </span>
                <span wire:loading wire:target="sendMessage">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         style="animation:spin 1s linear infinite;">
                        <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </span>
            </button>
        </div>
        @endif
    </div>
    @endif
</div>
