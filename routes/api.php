<?php

use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Projects API
    Route::get('/projects', function (Request $request) {
        $user = $request->user();
        $projects = Project::where('owner_id', $user->id)
            ->orWhereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            })
            ->with('owner', 'status')
            ->withCount('tickets')
            ->get();

        return response()->json($projects);
    });

    Route::get('/projects/{project}/tickets', function (Request $request, Project $project) {
        $user = $request->user();

        // Verify access
        if ($project->owner_id !== $user->id && !$project->users()->where('users.id', $user->id)->exists()) {
            abort(403);
        }

        $tickets = $project->tickets()
            ->with(['owner', 'responsible', 'status', 'type', 'priority'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 25));

        return response()->json($tickets);
    });

    // Tickets API
    Route::get('/tickets/{ticket}', function (Request $request, Ticket $ticket) {
        $user = $request->user();

        if (!$user->can('view', $ticket)) {
            abort(403);
        }

        $ticket->load(['owner', 'responsible', 'status', 'type', 'priority', 'project', 'epic', 'sprint']);

        return response()->json($ticket);
    });

    Route::post('/tickets', function (Request $request) {
        $user = $request->user();

        if (!$user->can('Create ticket')) {
            abort(403);
        }

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

        // Verify project access
        $project = Project::findOrFail($validated['project_id']);
        if ($project->owner_id !== $user->id && !$project->users()->where('users.id', $user->id)->exists()) {
            abort(403);
        }

        // Set defaults
        $validated['owner_id'] = $user->id;
        if (!isset($validated['status_id'])) {
            $validated['status_id'] = TicketStatus::where('is_default', true)->first()?->id;
        }
        if (!isset($validated['type_id'])) {
            $validated['type_id'] = TicketType::where('is_default', true)->first()?->id;
        }
        if (!isset($validated['priority_id'])) {
            $validated['priority_id'] = TicketPriority::where('is_default', true)->first()?->id;
        }

        $ticket = Ticket::create($validated);
        $ticket->load(['owner', 'responsible', 'status', 'type', 'priority', 'project']);

        return response()->json($ticket, 201);
    });
});
