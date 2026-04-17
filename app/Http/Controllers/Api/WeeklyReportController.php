<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WeeklyReportResource;
use App\Models\WeeklyReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WeeklyReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = WeeklyReport::query()
            ->with(['user', 'project', 'acknowledgedByUser']);

        if ($request->boolean('mine', true)) {
            $query->where('user_id', $user->id);
        }

        if ($projectId = $request->get('project_id')) {
            $query->where('project_id', $projectId);
        }

        $reports = $query->orderByDesc('week_start')
            ->paginate((int) $request->get('per_page', 25));

        return response()->json([
            'data' => WeeklyReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function show(Request $request, WeeklyReport $weeklyReport): JsonResponse
    {
        $this->authorizeReportAccess($request, $weeklyReport);

        $weeklyReport->load(['user', 'project', 'acknowledgedByUser']);

        return response()->json([
            'data' => new WeeklyReportResource($weeklyReport),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'week_start' => [
                'required', 'date',
                Rule::unique('weekly_reports')->where(fn($q) => $q->where('user_id', $user->id)),
            ],
            'week_end' => 'required|date|after_or_equal:week_start',
            'content' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted',
        ]);

        $validated['user_id'] = $user->id;
        $validated['status'] ??= 'draft';
        if ($validated['status'] === 'submitted') {
            $validated['submitted_at'] = now();
        }

        $report = WeeklyReport::create($validated);
        $report->load(['user', 'project']);

        return response()->json([
            'data' => new WeeklyReportResource($report),
        ], 201);
    }

    public function update(Request $request, WeeklyReport $weeklyReport): JsonResponse
    {
        $this->authorizeOwnerOnly($request, $weeklyReport);

        $validated = $request->validate([
            'content' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted',
        ]);

        if (($validated['status'] ?? null) === 'submitted' && $weeklyReport->status !== 'submitted') {
            $validated['submitted_at'] = now();
        }

        $weeklyReport->update($validated);
        $weeklyReport->load(['user', 'project']);

        return response()->json([
            'data' => new WeeklyReportResource($weeklyReport),
        ]);
    }

    public function destroy(Request $request, WeeklyReport $weeklyReport): JsonResponse
    {
        $this->authorizeOwnerOnly($request, $weeklyReport);

        $weeklyReport->delete();

        return response()->json(['message' => 'Report deleted']);
    }

    protected function authorizeReportAccess(Request $request, WeeklyReport $report): void
    {
        $user = $request->user();
        if ($report->user_id === $user->id) return;

        $project = $report->project;
        if ($project && ($project->owner_id === $user->id
            || $project->users()->where('users.id', $user->id)->exists())) {
            return;
        }

        abort(403, 'You do not have access to this report.');
    }

    protected function authorizeOwnerOnly(Request $request, WeeklyReport $report): void
    {
        abort_unless($report->user_id === $request->user()->id, 403, 'Only the report owner can modify this.');
    }
}
