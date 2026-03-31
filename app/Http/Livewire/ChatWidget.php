<?php

namespace App\Http\Livewire;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatBotService;
use Livewire\Component;

class ChatWidget extends Component
{
    public bool $isOpen = false;
    public bool $showLanguagePicker = true;
    public ?string $language = null;
    public string $message = '';
    public ?int $conversationId = null;
    public array $chatMessages = [];
    public bool $isLoading = false;

    public function mount()
    {
        $convId = session('chat_conversation_id');
        if ($convId) {
            $conversation = ChatConversation::where('id', $convId)
                ->where('user_id', auth()->id())
                ->first();

            if ($conversation) {
                $this->conversationId = $conversation->id;
                $this->language = $conversation->language;
                $this->showLanguagePicker = false;
                $this->loadMessages();
            } else {
                session()->forget('chat_conversation_id');
            }
        }
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function selectLanguage(string $lang)
    {
        $this->language = $lang;
        $this->showLanguagePicker = false;

        $conversation = ChatConversation::create([
            'user_id' => auth()->id(),
            'language' => $lang,
        ]);

        $this->conversationId = $conversation->id;
        session(['chat_conversation_id' => $conversation->id]);

        // Welcome message
        $welcome = $lang === 'id'
            ? 'Halo ' . auth()->user()->name . '! Saya asisten PM Anda. Saya bisa membantu menjawab pertanyaan tentang project, tiket, dan juga menerima feedback. Ada yang bisa saya bantu?'
            : 'Hello ' . auth()->user()->name . '! I am your PM assistant. I can help answer questions about projects, tickets, and also receive feedback. How can I help you?';

        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $welcome,
        ]);

        $this->loadMessages();
    }

    public function sendMessage()
    {
        $text = trim($this->message);
        if (empty($text) || !$this->conversationId) {
            return;
        }

        $this->message = '';

        // Save user message
        ChatMessage::create([
            'conversation_id' => $this->conversationId,
            'role' => 'user',
            'content' => $text,
        ]);

        // Update conversation title from first user message
        $conversation = ChatConversation::find($this->conversationId);
        if ($conversation && !$conversation->title) {
            $conversation->update(['title' => \Illuminate\Support\Str::limit($text, 80)]);
        }

        $this->loadMessages();
        $this->isLoading = true;

        // Get AI response
        $service = new ChatBotService();
        $result = $service->chat($conversation);

        // Save assistant response
        ChatMessage::create([
            'conversation_id' => $this->conversationId,
            'role' => 'assistant',
            'content' => $result['content'],
            'metadata' => $result['metadata'],
        ]);

        $this->isLoading = false;
        $this->loadMessages();

        // Emit event for auto-scroll
        $this->emit('chatMessageReceived');
    }

    public function newConversation()
    {
        $this->conversationId = null;
        $this->chatMessages = [];
        $this->showLanguagePicker = true;
        $this->language = null;
        $this->message = '';
        session()->forget('chat_conversation_id');
    }

    private function loadMessages()
    {
        if (!$this->conversationId) {
            $this->chatMessages = [];
            return;
        }

        $this->chatMessages = ChatMessage::where('conversation_id', $this->conversationId)
            ->orderBy('created_at')
            ->get()
            ->map(fn(ChatMessage $m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'time' => $m->created_at->format('H:i'),
                'metadata' => $m->metadata,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.chat-widget');
    }
}
