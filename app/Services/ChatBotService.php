<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\CustomerFeedback;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\WeeklyReport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
                'suggest_project_change' => $this->executeSuggestProjectChange($conversation, $arguments),
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
            if (isset($result['change_type'])) {
                $metadata['feedback_id'] = $result['feedback_id'] ?? null;
                $metadata['change_type'] = $result['change_type'];
                $metadata['type'] = 'change_suggested';
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

    private function executeSuggestProjectChange(ChatConversation $conversation, array $args): array
    {
        $projectName = $args['project_name'] ?? '';
        $field = $args['field'] ?? '';
        $proposedContent = $args['proposed_content'] ?? '';
        $reason = $args['reason'] ?? '';

        if (!in_array($field, ['description', 'goals'])) {
            return ['error' => 'Invalid field. Must be "description" or "goals".'];
        }

        $project = Project::where('name', 'like', '%' . $projectName . '%')->first();
        if (!$project) {
            $project = Project::where('owner_id', $conversation->user_id)
                ->orWhereHas('users', fn($q) => $q->where('users.id', $conversation->user_id))
                ->first();
        }

        if (!$project) {
            return ['error' => 'No accessible project found.'];
        }

        $currentValue = $field === 'description' ? $project->description : $project->goals;
        $changeType = $field === 'description' ? 'project_description' : 'project_goals';
        $fieldLabel = $field === 'description' ? 'Description' : 'Goals & Requirements';

        $feedback = CustomerFeedback::create([
            'project_id' => $project->id,
            'user_id' => $conversation->user_id,
            'title' => $fieldLabel . ' Change Request - ' . $project->name,
            'description' => $reason,
            'status' => 'pending',
            'change_type' => $changeType,
            'proposed_data' => [
                'field' => $field,
                'old_value' => $currentValue,
                'new_value' => $proposedContent,
            ],
        ]);

        return [
            'success' => true,
            'feedback_id' => $feedback->id,
            'change_type' => $changeType,
            'project_name' => $project->name,
            'message' => $fieldLabel . ' change request #' . $feedback->id . ' submitted for project: ' . $project->name . '. A Project Manager will review the proposed changes.',
        ];
    }

    private function buildSystemPrompt(ChatConversation $conversation): string
    {
        $user = $conversation->user;
        $roles = $user->roles->pluck('name')->implode(', ') ?: 'User';
        $lang = $conversation->language === 'id' ? 'Bahasa Indonesia' : 'English';

        // Load projects with tickets, media, and status
        $projects = Project::where('owner_id', $user->id)
            ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
            ->with([
                'status',
                'media',
                'tickets' => fn($q) => $q->with(['status', 'priority', 'responsible', 'epic'])->latest()->limit(60),
            ])
            ->get();

        $projectContext = '';
        $pdfExtractor = new PdfExtractorService();

        foreach ($projects as $project) {
            $desc = strip_tags($project->description ?? '');
            $goals = strip_tags($project->goals ?? '');

            $projectContext .= "\n\n===== PROJECT: {$project->name} =====";
            $projectContext .= "\nStatus: " . ($project->status->name ?? 'N/A');
            $projectContext .= "\nType: " . ucfirst($project->type ?? 'kanban');

            if ($desc) {
                $projectContext .= "\nDescription: " . Str::limit($desc, 300);
            }

            // Project Goals
            if ($goals) {
                $projectContext .= "\n\nPROJECT GOALS & REQUIREMENTS:";
                $projectContext .= "\n" . Str::limit($goals, 2000);
            }

            // Project Documents (PDF text)
            $documents = $project->getMedia('documents');
            if ($documents->count() > 0) {
                $projectContext .= "\n\nPROJECT DOCUMENTS:";
                foreach ($documents as $doc) {
                    $projectContext .= "\n--- Document: {$doc->file_name} ({$doc->human_readable_size}) ---";
                    $text = $pdfExtractor->extractFromMedia($doc, 3000);
                    $projectContext .= "\n" . $text;
                }
            }

            // Tickets
            $totalTickets = $project->tickets->count();
            $projectContext .= "\n\nTICKETS ({$totalTickets} shown):";
            foreach ($project->tickets as $ticket) {
                $assignee = $ticket->responsible->name ?? 'Unassigned';
                $status = $ticket->status->name ?? 'Unknown';
                $priority = $ticket->priority->name ?? 'Normal';
                $epic = $ticket->epic->name ?? '-';
                $due = $ticket->due_date ? $ticket->due_date->format('Y-m-d') : '-';
                $desc = Str::limit(strip_tags($ticket->content ?? ''), 100);
                $projectContext .= "\n  [{$ticket->code}] {$ticket->name}";
                $projectContext .= "\n    Status: {$status} | Priority: {$priority} | Assignee: {$assignee} | Epic: {$epic} | Due: {$due}";
                if ($desc && $desc !== '-') {
                    $projectContext .= "\n    Summary: {$desc}";
                }
            }
        }

        // Weekly Reports (last 4 weeks across all projects)
        $weeklyReports = WeeklyReport::where('user_id', $user->id)
            ->orWhereHas('project', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('users', fn($q2) => $q2->where('users.id', $user->id));
            })
            ->with(['user', 'project', 'media'])
            ->orderByDesc('week_start')
            ->limit(8)
            ->get();

        $weeklyContext = '';
        if ($weeklyReports->count() > 0) {
            $weeklyContext .= "\n\n===== WEEKLY REPORTS =====";
            foreach ($weeklyReports as $report) {
                $weeklyContext .= "\n\n--- Week: {$report->week_start->format('Y-m-d')} to {$report->week_end->format('Y-m-d')} ---";
                $weeklyContext .= "\nAuthor: " . ($report->user->name ?? 'Unknown');
                $weeklyContext .= "\nProject: " . ($report->project->name ?? 'General');
                $weeklyContext .= "\nStatus: {$report->status}";

                if ($report->content) {
                    $weeklyContext .= "\nContent: " . Str::limit(strip_tags($report->content), 500);
                }

                if ($report->auto_summary && is_array($report->auto_summary)) {
                    $weeklyContext .= "\nAuto Summary: " . json_encode($report->auto_summary);
                }

                // Weekly report PDF attachments
                $attachments = $report->getMedia('attachments');
                foreach ($attachments as $att) {
                    if (strtolower($att->mime_type) === 'application/pdf') {
                        $text = $pdfExtractor->extractFromMedia($att, 2000);
                        $weeklyContext .= "\nAttachment ({$att->file_name}): {$text}";
                    }
                }
            }
        }

        return <<<PROMPT
You are a helpful project management assistant for CapellaDigicrats PM Helper.
You MUST respond in {$lang}.
IMPORTANT RULES:
- Never use em dashes in your responses. Use hyphens (-), commas, or periods instead.
- Be informative, clear, and helpful.
- Use markdown formatting for readability (bold, lists, etc.).
- When comparing goals vs tickets, analyze coverage gaps and progress.

Current User: {$user->name}
Role: {$roles}
{$projectContext}
{$weeklyContext}

CAPABILITIES:
You have access to project goals, uploaded documents (PDFs), ticket details, and weekly reports.
You can:
- Answer questions about project status, goals, and requirements
- Compare project goals against existing tickets to find gaps
- Summarize document contents and weekly reports
- Suggest task priorities based on goals and deadlines
- Identify tickets that may be at risk (overdue, unassigned, etc.)
- Submit customer feedback and feature requests
- Help users draft and submit changes to project description or goals

FEEDBACK HANDLING:
When the user shares feedback, suggestions, bug reports, or feature requests:
1. Check if it relates to any existing ticket listed above.
2. If related to an existing ticket, mention the ticket code and explain the connection.
3. If the user wants to submit this as customer feedback, use the create_customer_feedback function.
4. Always ask the user for confirmation before creating feedback.
5. Feedback is created with 'pending' status - a project manager must approve before any ticket is created.
6. If feedback relates to a specific ticket, include the ticket code in related_ticket_code.

PROJECT CHANGE SUGGESTIONS:
When the user (especially Stakeholders) wants to change the project description or goals/requirements:
1. Show them the CURRENT content of the field they want to change.
2. Discuss what they want to change and why. Help them refine their proposed changes.
3. Compare the current content with what they propose. Point out what is being added, removed, or modified.
4. Collaborate with them to reach a final version they are happy with.
5. Once the user confirms the final version, use the suggest_project_change function to submit it.
6. The change will be submitted as a pending request for Project Manager approval.
7. IMPORTANT: The proposed_content must be the COMPLETE new version of the field (not just the diff). It should be in HTML format suitable for a rich text editor.
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
            [
                'type' => 'function',
                'function' => [
                    'name' => 'suggest_project_change',
                    'description' => 'Submit a proposed change to a project\'s description or goals/requirements. Use this after discussing and finalizing the changes with the user. The change will be submitted for Project Manager approval.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'project_name' => [
                                'type' => 'string',
                                'description' => 'The project name',
                            ],
                            'field' => [
                                'type' => 'string',
                                'enum' => ['description', 'goals'],
                                'description' => 'Which field to change: "description" for project description, "goals" for project goals & requirements',
                            ],
                            'proposed_content' => [
                                'type' => 'string',
                                'description' => 'The COMPLETE new content for the field in HTML format. This replaces the entire field, not a partial update.',
                            ],
                            'reason' => [
                                'type' => 'string',
                                'description' => 'Summary of why this change is needed and what was discussed',
                            ],
                        ],
                        'required' => ['project_name', 'field', 'proposed_content', 'reason'],
                    ],
                ],
            ],
        ];
    }
}
