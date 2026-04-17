<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiscussionResource;
use App\Models\Discussion;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Discussion::query()
            ->with(['user', 'project', 'ticket', 'resolvedByUser'])
            ->withCount('replies');

        if ($projectId = $request->get('project_id')) {
            $project = Project::findOrFail($projectId);
            $this->authorizeProjectAccess($request, $project);
            $query->where('project_id', $projectId);
        } else {
            $query->whereHas('project', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('users', fn($inner) => $inner->where('users.id', $user->id));
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $discussions = $query->orderByDesc('created_at')
            ->paginate((int) $request->get('per_page', 25));

        return response()->json([
            'data' => DiscussionResource::collection($discussions),
            'meta' => [
                'current_page' => $discussions->currentPage(),
                'last_page' => $discussions->lastPage(),
                'per_page' => $discussions->perPage(),
                'total' => $discussions->total(),
            ],
        ]);
    }

    public function show(Request $request, Discussion $discussion): JsonResponse
    {
        $this->authorizeProjectAccess($request, $discussion->project);

        $discussion->load([
            'user', 'project', 'ticket', 'resolvedByUser',
            'replies' => fn($q) => $q->with('user')->orderBy('created_at'),
        ]);

        return response()->json([
            'data' => new DiscussionResource($discussion),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'ticket_id' => 'nullable|exists:tickets,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $this->authorizeProjectAccess($request, $project);

        $validated['user_id'] = $user->id;
        $validated['status'] ??= 'open';

        $discussion = Discussion::create($validated);
        $discussion->load(['user', 'project', 'ticket']);

        return response()->json([
            'data' => new DiscussionResource($discussion),
        ], 201);
    }

    public function update(Request $request, Discussion $discussion): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $discussion->user_id === $user->id
                || $discussion->project->owner_id === $user->id,
            403,
            'Only the discussion creator or project owner can edit.'
        );

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'priority' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
        ]);

        if (($validated['status'] ?? null) === 'resolved' && $discussion->status !== 'resolved') {
            $validated['resolved_by'] = $user->id;
            $validated['resolved_at'] = now();
        }

        $discussion->update($validated);
        $discussion->load(['user', 'project', 'ticket', 'resolvedByUser']);

        return response()->json([
            'data' => new DiscussionResource($discussion),
        ]);
    }

    public function destroy(Request $request, Discussion $discussion): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $discussion->user_id === $user->id
                || $discussion->project->owner_id === $user->id,
            403
        );

        $discussion->delete();

        return response()->json(['message' => 'Discussion deleted']);
    }

    protected function authorizeProjectAccess(Request $request, ?Project $project): void
    {
        if (! $project) abort(404);

        $user = $request->user();
        $hasAccess = $project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists();

        abort_unless($hasAccess, 403, 'You do not have access to this project.');
    }
}
