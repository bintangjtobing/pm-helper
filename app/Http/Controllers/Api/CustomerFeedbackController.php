<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerFeedbackCommentResource;
use App\Http\Resources\CustomerFeedbackResource;
use App\Models\CustomerFeedback;
use App\Models\CustomerFeedbackActivity;
use App\Models\CustomerFeedbackComment;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Notifications\FeedbackConverted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerFeedbackController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = CustomerFeedback::query()
            ->with(['user', 'project', 'convertedTicket'])
            ->withCount('comments');

        if ($projectId = $request->get('project_id')) {
            $project = Project::findOrFail($projectId);
            $this->authorizeProjectAccess($request, $project);
            $query->where('project_id', $projectId);
        } else {
            $isAdmin = $user->hasRole(['Super Admin', 'Admin']);
            if (! $isAdmin) {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhereHas('project', function ($inner) use ($user) {
                            $inner->where('owner_id', $user->id)
                                ->orWhereHas('users', fn ($p) => $p->where('users.id', $user->id));
                        });
                });
            }
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $feedbacks = $query->orderByDesc('created_at')
            ->paginate((int) $request->get('per_page', 25));

        return response()->json([
            'data' => CustomerFeedbackResource::collection($feedbacks),
            'meta' => [
                'current_page' => $feedbacks->currentPage(),
                'last_page' => $feedbacks->lastPage(),
                'per_page' => $feedbacks->perPage(),
                'total' => $feedbacks->total(),
            ],
        ]);
    }

    public function show(Request $request, CustomerFeedback $feedback): JsonResponse
    {
        $this->authorizeFeedbackAccess($request, $feedback);

        $feedback->load([
            'user', 'project', 'convertedTicket',
            'comments' => fn ($q) => $q->with('user')->orderBy('created_at'),
            'activities' => fn ($q) => $q->with('user')->orderByDesc('created_at'),
        ]);

        return response()->json([
            'data' => new CustomerFeedbackResource($feedback),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'change_type' => 'nullable|in:project_description,project_goals',
            'proposed_data' => 'nullable|array',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $this->authorizeProjectAccess($request, $project);

        $validated['user_id'] = $user->id;
        $validated['status'] = 'pending';

        $feedback = CustomerFeedback::create($validated);
        $feedback->load(['user', 'project']);

        return response()->json([
            'data' => new CustomerFeedbackResource($feedback),
        ], 201);
    }

    public function update(Request $request, CustomerFeedback $feedback): JsonResponse
    {
        $user = $request->user();
        $isAdmin = $user->hasRole(['Super Admin', 'Admin']);
        abort_unless(
            $isAdmin || ($feedback->user_id === $user->id && $feedback->status === 'pending'),
            403,
            'Only admins or the submitter of a pending feedback can edit.'
        );

        $rules = [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
        ];
        if ($isAdmin) {
            $rules['status'] = 'sometimes|in:pending,converted_to_ticket,rejected';
        }

        $validated = $request->validate($rules);
        $feedback->update($validated);
        $feedback->load(['user', 'project', 'convertedTicket']);

        return response()->json([
            'data' => new CustomerFeedbackResource($feedback),
        ]);
    }

    public function destroy(Request $request, CustomerFeedback $feedback): JsonResponse
    {
        abort_unless($request->user()->hasRole(['Super Admin', 'Admin']), 403);

        $feedback->delete();

        return response()->json(['message' => 'Feedback deleted']);
    }

    public function convert(Request $request, CustomerFeedback $feedback): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole(['Super Admin', 'Admin']), 403, 'Only admins can convert feedback.');
        abort_unless($feedback->status === 'pending', 422, 'Feedback is not pending.');

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'type_id' => 'required|exists:ticket_types,id',
            'priority_id' => 'required|exists:ticket_priorities,id',
        ]);

        $backlog = TicketStatus::where('name', 'LIKE', '%backlog%')->first()
            ?? TicketStatus::where('is_default', true)->first();

        abort_unless($backlog, 500, 'No ticket status available.');

        $ticket = Ticket::create([
            'name' => $validated['title'] ?? $feedback->title,
            'content' => ($validated['description'] ?? $feedback->description) . "\n\n[From direct customer feedback]",
            'project_id' => $feedback->project_id,
            'owner_id' => $user->id,
            'responsible_id' => $feedback->user_id,
            'status_id' => $backlog->id,
            'type_id' => $validated['type_id'],
            'priority_id' => $validated['priority_id'],
        ]);

        $feedback->update([
            'status' => 'converted_to_ticket',
            'converted_ticket_id' => $ticket->id,
        ]);

        CustomerFeedbackActivity::create([
            'feedback_id' => $feedback->id,
            'user_id' => $user->id,
            'action' => 'converted_to_ticket',
            'notes' => "Converted to ticket: {$ticket->code}",
        ]);

        if ($feedback->user) {
            $feedback->user->notify(new FeedbackConverted($feedback));
        }

        $feedback->load(['user', 'project', 'convertedTicket']);

        return response()->json([
            'data' => new CustomerFeedbackResource($feedback),
        ]);
    }

    public function listComments(Request $request, CustomerFeedback $feedback): JsonResponse
    {
        $this->authorizeFeedbackAccess($request, $feedback);

        $comments = $feedback->comments()
            ->with('user')
            ->orderBy('created_at')
            ->paginate((int) $request->get('per_page', 50));

        return response()->json([
            'data' => CustomerFeedbackCommentResource::collection($comments),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ],
        ]);
    }

    public function storeComment(Request $request, CustomerFeedback $feedback): JsonResponse
    {
        $this->authorizeFeedbackAccess($request, $feedback);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment = CustomerFeedbackComment::create([
            'feedback_id' => $feedback->id,
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');

        return response()->json([
            'data' => new CustomerFeedbackCommentResource($comment),
        ], 201);
    }

    public function destroyComment(Request $request, CustomerFeedbackComment $comment): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $comment->user_id === $user->id || $user->hasRole(['Super Admin', 'Admin']),
            403
        );

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }

    protected function authorizeProjectAccess(Request $request, ?Project $project): void
    {
        if (! $project) abort(404);

        $user = $request->user();
        if ($user->hasRole(['Super Admin', 'Admin'])) return;

        $hasAccess = $project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists();

        abort_unless($hasAccess, 403, 'You do not have access to this project.');
    }

    protected function authorizeFeedbackAccess(Request $request, CustomerFeedback $feedback): void
    {
        $user = $request->user();
        if ($user->hasRole(['Super Admin', 'Admin'])) return;
        if ($feedback->user_id === $user->id) return;

        $project = $feedback->project;
        $hasAccess = $project && (
            $project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists()
        );

        abort_unless($hasAccess, 403, 'You do not have access to this feedback.');
    }
}
