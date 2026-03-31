<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\CustomerFeedback;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatBotService
{
    private string $apiKey;
    private string $model = 'gpt-4o';
    private string $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key', '');
    }

    public function chat(ChatConversation $conversation): array
    {
        if (empty($this->apiKey)) {
            return [
                'content' => $conversation->language === 'id'
                    ? 'Maaf, layanan chat belum dikonfigurasi. Hubungi administrator.'
                    : 'Sorry, chat service is not configured. Contact administrator.',
                'metadata' => null,
            ];
        }

        try {
            $systemPrompt = $this->buildSystemPrompt($conversation);
            $messages = $this->buildMessages($conversation, $systemPrompt);
            $tools = $this->getTools();

            $payload = [
                'model' => $this->model,
                'messages' => $messages,
                'tools' => $tools,
                'temperature' => 0.7,
                'max_tokens' => 1500,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(60)->post($this->apiUrl, $payload);

            if ($response->failed()) {
                Log::error('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
                return [
                    'content' => $conversation->language === 'id'
                        ? 'Maaf, terjadi kesalahan saat memproses pesan Anda. Silakan coba lagi.'
                        : 'Sorry, an error occurred while processing your message. Please try again.',
                    'metadata' => null,
                ];
            }

            $data = $response->json();
            $choice = $data['choices'][0] ?? null;

            if (!$choice) {
                return ['content' => 'No response received.', 'metadata' => null];
            }

            // Handle tool calls (function calling)
            if (isset($choice['message']['tool_calls'])) {
                return $this->handleToolCalls($conversation, $choice['message'], $messages);
            }

            return [
                'content' => $choice['message']['content'] ?? '',
                'metadata' => null,
            ];
        } catch (\Exception $e) {
            Log::error('ChatBot error', ['error' => $e->getMessage()]);
            return [
                'content' => $conversation->language === 'id'
                    ? 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.'
                    : 'Sorry, an error occurred. Please try again later.',
                'metadata' => null,
            ];
        }
    }

    private function handleToolCalls(ChatConversation $conversation, array $assistantMessage, array $messages): array
    {
        $toolResults = [];
        $metadata = [];

        foreach ($assistantMessage['tool_calls'] as $toolCall) {
            $functionName = $toolCall['function']['name'];
            $arguments = json_decode($toolCall['function']['arguments'], true) ?? [];

            $result = match ($functionName) {
                'create_customer_feedback' => $this->executeCreateFeedback($conversation, $arguments),
                'link_feedback_to_ticket' => $this->executeLinkFeedback($conversation, $arguments),
                default => ['error' => 'Unknown function: ' . $functionName],
            };

            $toolResults[] = [
                'role' => 'tool',
                'tool_call_id' => $toolCall['id'],
                'content' => json_encode($result),
            ];

            if (isset($result['feedback_id'])) {
                $metadata['feedback_id'] = $result['feedback_id'];
                $metadata['type'] = 'feedback_created';
            }
            if (isset($result['linked_ticket'])) {
                $metadata['linked_ticket'] = $result['linked_ticket'];
                $metadata['type'] = 'feedback_linked';
            }
        }

        // Send tool results back to get final response
        $messages[] = $assistantMessage;
        $messages = array_merge($messages, $toolResults);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
        ])->timeout(60)->post($this->apiUrl, [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1500,
        ]);

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? 'Response processed.';

        return [
            'content' => $content,
            'metadata' => $metadata ?: null,
        ];
    }

    private function executeCreateFeedback(ChatConversation $conversation, array $args): array
    {
        $project = Project::where('name', 'like', '%' . ($args['project_name'] ?? '') . '%')->first();
        if (!$project) {
            // Fallback: get the first project the user has access to
            $project = Project::where('owner_id', $conversation->user_id)
                ->orWhereHas('users', fn($q) => $q->where('users.id', $conversation->user_id))
                ->first();
        }

        if (!$project) {
            return ['error' => 'No accessible project found.'];
        }

        $feedback = CustomerFeedback::create([
            'project_id' => $project->id,
            'user_id' => $conversation->user_id,
            'title' => $args['title'] ?? 'Untitled Feedback',
            'description' => $args['description'] ?? '',
            'status' => 'pending',
        ]);

        // Link to existing ticket if specified
        if (!empty($args['related_ticket_code'])) {
            $ticket = Ticket::where('code', $args['related_ticket_code'])->first();
            if ($ticket) {
                $feedback->update(['converted_ticket_id' => $ticket->id]);
            }
        }

        return [
            'success' => true,
            'feedback_id' => $feedback->id,
            'project_name' => $project->name,
            'message' => 'Customer feedback #' . $feedback->id . ' created with pending status for project: ' . $project->name,
        ];
    }

    private function executeLinkFeedback(ChatConversation $conversation, array $args): array
    {
        $ticketCode = $args['ticket_code'] ?? '';
        $ticket = Ticket::where('code', $ticketCode)->first();

        if (!$ticket) {
            return ['error' => 'Ticket ' . $ticketCode . ' not found.'];
        }

        // Add comment/activity to the ticket
        $note = $args['feedback_note'] ?? 'Feedback from chat';

        // Create a customer feedback linked to this ticket
        $feedback = CustomerFeedback::create([
            'project_id' => $ticket->project_id,
            'user_id' => $conversation->user_id,
            'title' => 'Chat feedback for ' . $ticketCode,
            'description' => $note,
            'status' => 'pending',
            'converted_ticket_id' => $ticket->id,
        ]);

        return [
            'success' => true,
            'feedback_id' => $feedback->id,
            'linked_ticket' => $ticketCode,
            'message' => 'Feedback linked to ticket ' . $ticketCode . ' and pending PM approval.',
        ];
    }

    private function buildSystemPrompt(ChatConversation $conversation): string
    {
        $user = $conversation->user;
        $roles = $user->roles->pluck('name')->implode(', ') ?: 'User';
        $lang = $conversation->language === 'id' ? 'Bahasa Indonesia' : 'English';

        $projects = Project::where('owner_id', $user->id)
            ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
            ->with(['status', 'tickets' => fn($q) => $q->with(['status', 'priority', 'responsible'])->latest()->limit(50)])
            ->get();

        $projectContext = '';
        foreach ($projects as $project) {
            $desc = strip_tags($project->description ?? '');
            $projectContext .= "\n\nProject: {$project->name}";
            $projectContext .= "\n  Status: " . ($project->status->name ?? 'N/A');
            $projectContext .= "\n  Type: " . ucfirst($project->type ?? 'kanban');
            if ($desc) {
                $projectContext .= "\n  Description: " . \Illuminate\Support\Str::limit($desc, 200);
            }
            $projectContext .= "\n  Tickets (" . $project->tickets->count() . " shown):";

            foreach ($project->tickets as $ticket) {
                $assignee = $ticket->responsible->name ?? 'Unassigned';
                $status = $ticket->status->name ?? 'Unknown';
                $priority = $ticket->priority->name ?? 'Normal';
                $due = $ticket->due_date ? $ticket->due_date->format('Y-m-d') : '-';
                $projectContext .= "\n    [{$ticket->code}] {$ticket->name} | {$status} | {$priority} | {$assignee} | Due: {$due}";
            }
        }

        return <<<PROMPT
You are a helpful project management assistant for CapellaDigicrats PM Helper.
You MUST respond in {$lang}.
IMPORTANT RULES:
- Never use em dashes in your responses. Use hyphens (-), commas, or periods instead.
- Be informative, clear, and helpful.
- Format responses with short paragraphs. Use bullet points for lists.

Current User: {$user->name}
Role: {$roles}
{$projectContext}

FEEDBACK HANDLING:
When the user shares feedback, suggestions, bug reports, or feature requests:
1. Check if it relates to any existing ticket listed above.
2. If related to an existing ticket, mention the ticket code and explain the connection.
3. If the user wants to submit this as customer feedback, use the create_customer_feedback function.
4. Always ask the user for confirmation before creating feedback.
5. Feedback is created with 'pending' status - a project manager must approve before any ticket is created.
6. If feedback relates to a specific ticket, include the ticket code in related_ticket_code.

You can help users with:
- Understanding project status and ticket details
- Suggesting task priorities
- Answering questions about the project workflow
- Submitting customer feedback and feature requests
- General project management guidance
PROMPT;
    }

    private function buildMessages(ChatConversation $conversation, string $systemPrompt): array
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Last 20 messages for context
        $chatMessages = $conversation->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->sortBy('id');

        foreach ($chatMessages as $msg) {
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        return $messages;
    }

    private function getTools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_customer_feedback',
                    'description' => 'Create a customer feedback entry. Use when the user explicitly wants to submit feedback, a feature request, or a bug report. Always confirm with the user first before calling this.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'project_name' => [
                                'type' => 'string',
                                'description' => 'The project name the feedback is for',
                            ],
                            'title' => [
                                'type' => 'string',
                                'description' => 'Short title summarizing the feedback (max 255 chars)',
                            ],
                            'description' => [
                                'type' => 'string',
                                'description' => 'Detailed description of the feedback',
                            ],
                            'related_ticket_code' => [
                                'type' => 'string',
                                'description' => 'If related to an existing ticket, the ticket code (e.g. QOS-76). Leave empty if not related.',
                            ],
                        ],
                        'required' => ['project_name', 'title', 'description'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'link_feedback_to_ticket',
                    'description' => 'Link user feedback directly to an existing ticket when the feedback is specifically about that ticket.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'ticket_code' => [
                                'type' => 'string',
                                'description' => 'The ticket code (e.g. QOS-76)',
                            ],
                            'feedback_note' => [
                                'type' => 'string',
                                'description' => 'The feedback note to record',
                            ],
                        ],
                        'required' => ['ticket_code', 'feedback_note'],
                    ],
                ],
            ],
        ];
    }
}
