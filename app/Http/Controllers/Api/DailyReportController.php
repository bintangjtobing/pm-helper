<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DailyReportResource;
use App\Models\DailyReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DailyReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = DailyReport::query()
            ->with(['user', 'project', 'acknowledgedByUser']);

        if ($request->boolean('mine', true)) {
            $query->where('user_id', $user->id);
        }

        if ($projectId = $request->get('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('report_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('report_date', '<=', $to);
        }

        $reports = $query->orderByDesc('report_date')
            ->paginate((int) $request->get('per_page', 25));

        return response()->json([
            'data' => DailyReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function show(Request $request, DailyReport $dailyReport): JsonResponse
    {
        $this->authorizeReportAccess($request, $dailyReport);

        $dailyReport->load(['user', 'project', 'acknowledgedByUser']);

        return response()->json([
            'data' => new DailyReportResource($dailyReport),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'report_date' => [
                'required', 'date',
                Rule::unique('daily_reports')->where(fn($q) => $q
                    ->where('user_id', $user->id)
                    ->where('project_id', $request->input('project_id'))),
            ],
            'accomplished' => 'nullable|string',
            'plans' => 'nullable|string',
            'blockers' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted',
        ]);

        $validated['user_id'] = $user->id;
        $validated['status'] ??= 'draft';
        if ($validated['status'] === 'submitted') {
            $validated['submitted_at'] = now();
        }

        $report = DailyReport::create($validated);
        $report->load(['user', 'project']);

        return response()->json([
            'data' => new DailyReportResource($report),
        ], 201);
    }

    public function update(Request $request, DailyReport $dailyReport): JsonResponse
    {
        $this->authorizeOwnerOnly($request, $dailyReport);

        $validated = $request->validate([
            'accomplished' => 'nullable|string',
            'plans' => 'nullable|string',
            'blockers' => 'nullable|string',
            'status' => 'nullable|in:draft,submitted',
        ]);

        if (($validated['status'] ?? null) === 'submitted' && $dailyReport->status !== 'submitted') {
            $validated['submitted_at'] = now();
        }

        $dailyReport->update($validated);
        $dailyReport->load(['user', 'project']);

        return response()->json([
            'data' => new DailyReportResource($dailyReport),
        ]);
    }

    public function destroy(Request $request, DailyReport $dailyReport): JsonResponse
    {
        $this->authorizeOwnerOnly($request, $dailyReport);

        $dailyReport->delete();

        return response()->json(['message' => 'Report deleted']);
    }

    protected function authorizeReportAccess(Request $request, DailyReport $report): void
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

    protected function authorizeOwnerOnly(Request $request, DailyReport $report): void
    {
        abort_unless($report->user_id === $request->user()->id, 403, 'Only the report owner can modify this.');
    }
}
