<?php

namespace App\Http\Controllers\Api;

use App\Events\TicketMoved;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function indexByProject(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProjectAccess($request, $project);

        $query = $project->tickets()
            ->with(['owner', 'responsible', 'status', 'type', 'priority', 'epic']);

        if ($statusId = $request->get('status_id')) {
            $query->where('status_id', $statusId);
        }
        if ($assignedToMe = $request->boolean('mine')) {
            $user = $request->user();
            $query->where(fn($q) => $q
                ->where('owner_id', $user->id)
                ->orWhere('responsible_id', $user->id));
        }

        $sortBy = $request->get('sort_by', 'updated_at');
        match ($sortBy) {
            'created_at' => $query->orderByDesc('created_at'),
            'priority' => $query->orderBy('priority_id'),
            'due_date' => $query->orderByRaw('due_date IS NULL, due_date ASC'),
            'order' => $query->orderBy('order'),
            default => $query->orderByDesc('updated_at'),
        };

        $tickets = $query->paginate((int) $request->get('per_page', 25));

        return response()->json([
            'data' => TicketResource::collection($tickets),
            'meta' => [
                'current_page' => $tickets->currentPage(),
                'last_page' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ],
        ]);
    }

    public function show(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($request->user()->can('view', $ticket), 403);

        $ticket->load(['owner', 'responsible', 'status', 'type', 'priority', 'project', 'epic', 'sprint']);

        return response()->json([
            'data' => new TicketResource($ticket),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('Create ticket'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'project_id' => 'required|exists:projects,id',
            'responsible_id' => 'nullable|exists:users,id',
            'type_id' => 'nullable|exists:ticket_types,id',
            'priority_id' => 'nullable|exists:ticket_priorities,id',
            'status_id' => 'nullable|exists:ticket_statuses,id',
            'epic_id' => 'nullable|exists:epics,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'estimation' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $this->authorizeProjectAccess($request, $project);

        $validated['owner_id'] = $user->id;
        $validated['status_id'] ??= TicketStatus::where('is_default', true)->first()?->id;
        $validated['type_id'] ??= TicketType::where('is_default', true)->first()?->id;
        $validated['priority_id'] ??= TicketPriority::where('is_default', true)->first()?->id;

        $ticket = Ticket::create($validated);
        $ticket->load(['owner', 'responsible', 'status', 'type', 'priority', 'project']);

        return response()->json([
            'data' => new TicketResource($ticket),
        ], 201);
    }

    public function update(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($request->user()->can('update', $ticket), 403);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'responsible_id' => 'nullable|exists:users,id',
            'type_id' => 'nullable|exists:ticket_types,id',
            'priority_id' => 'nullable|exists:ticket_priorities,id',
            'status_id' => 'nullable|exists:ticket_statuses,id',
            'epic_id' => 'nullable|exists:epics,id',
            'sprint_id' => 'nullable|exists:sprints,id',
            'estimation' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);

        $ticket->update($validated);
        $ticket->load(['owner', 'responsible', 'status', 'type', 'priority', 'project']);

        return response()->json([
            'data' => new TicketResource($ticket),
        ]);
    }

    public function destroy(Request $request, Ticket $ticket): JsonResponse
    {
        abort_unless($request->user()->can('delete', $ticket), 403);

        $ticket->delete();

        return response()->json(['message' => 'Ticket deleted']);
    }

    public function move(Request $request, Ticket $ticket): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('update', $ticket), 403);

        $validated = $request->validate([
            'status_id' => 'required|exists:ticket_statuses,id',
            'order' => 'nullable|integer|min:0',
        ]);

        $targetStatus = TicketStatus::findOrFail($validated['status_id']);
        abort_unless($targetStatus->canBeSetByUser(), 403, 'You cannot move tickets to this status.');

        $oldStatusId = (int) $ticket->status_id;
        $newIndex = (int) ($validated['order'] ?? 0);

        $ticket->status_id = $targetStatus->id;
        $ticket->order = $newIndex;
        $ticket->save();

        broadcast(new TicketMoved(
            projectId: (int) $ticket->project_id,
            ticketId: (int) $ticket->id,
            oldStatusId: $oldStatusId,
            newStatusId: (int) $targetStatus->id,
            newIndex: $newIndex,
            movedByUserId: (int) $user->id,
        ))->toOthers();

        $ticket->load(['owner', 'responsible', 'status', 'type', 'priority', 'project']);

        return response()->json([
            'data' => new TicketResource($ticket),
        ]);
    }

    protected function authorizeProjectAccess(Request $request, Project $project): void
    {
        $user = $request->user();
        $hasAccess = $project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists();

        abort_unless($hasAccess, 403, 'You do not have access to this project.');
    }
}
