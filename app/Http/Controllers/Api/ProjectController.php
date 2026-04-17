<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $projects = Project::query()
            ->where(function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                    ->orWhereHas('users', fn($inner) => $inner->where('users.id', $user->id));
            })
            ->with(['owner', 'status'])
            ->withCount('tickets')
            ->withCount('users')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'data' => ProjectResource::collection($projects),
        ]);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProjectAccess($request, $project);

        $project->load(['owner', 'status'])
            ->loadCount(['tickets', 'users']);

        return response()->json([
            'data' => new ProjectResource($project),
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
