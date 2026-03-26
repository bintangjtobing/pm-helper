<?php

namespace App\Http\Controllers\RoadMap;

use App\Http\Controllers\Controller;
use App\Models\Epic;
use App\Models\Project;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class DataController extends Controller
{

    /**
     * Get project epics data
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function data(Project $project): JsonResponse
    {
        $project = Project::where(function ($query) {
            return $query->where('owner_id', auth()->user()->id)
                ->orWhereHas('users', function ($query) {
                    return $query->where('users.id', auth()->user()->id);
                });
        })->where('id', $project->id)->first();
        if (!$project) {
            return response()->json([]);
        }
        $epics = Epic::where('project_id', $project->id)->get();
        return response()->json($this->formatResponse($epics, $project));
    }

    /**
     * Format epics to JSON data
     *
     * @param Collection $epics
     * @return Collection
     */
    private function formatResponse(Collection $epics, Project $project): Collection
    {
        $results = collect();
        foreach ($epics->sortBy('id') as $epic) {
            $results->push(collect($this->epicObj($epic)));
            foreach ($epic->tickets as $ticket) {
                $results->push(collect($this->ticketObj($epic, $ticket)));
            }
        }
        $tickets = Ticket::where('project_id', $project->id)->whereNull('epic_id')
            ->orderBy('epic_id')->orderBy('id')->get();
        foreach ($tickets as $ticket) {
            $results->push(collect($this->ticketObj(null, $ticket)));
        }
        return $results;
    }

    /**
     * Format Epic object
     *
     * @param Epic $epic
     * @return array
     */
    private function epicObj(Epic $epic)
    {
        return [
            "pID" => $epic->id,
            "pName" => $epic->name,
            "pStart" => $epic->starts_at->format('Y-m-d'),
            "pEnd" => $epic->ends_at->format('Y-m-d') . " 23:59:59",
            "pClass" => "g-custom-task",
            "pLink" => "",
            "pMile" => 0,
            "pRes" => "",
            "pComp" => "",
            "pGroup" => 1,
            "pParent" => 0,
            "pOpen" => 1,
            "pDepend" => $epic->parent_id ?? "",
            "pCaption" => "",
            "pNotes" => "",
            "pBarText" => "",
            "meta" => [
                "id" => $epic->id,
                "epic" => true,
                "parent" => null,
                "slug" => null
            ]
        ];
    }

    /**
     * Format Ticket object
     *
     * @param Epic $epic
     * @param Ticket $ticket
     * @return array
     */
    private function ticketObj(Epic|null $epic, Ticket $ticket)
    {
        $statusName = $ticket->status?->name ?? '';

        // Determine completion and styling based on status
        $completedStatuses = ['Released', 'Approved', 'QA Passed', 'Ready for Release'];
        $inProgressStatuses = ['In Progress', 'Code Review', 'Fixing'];
        $qaStatuses = ['Ready for QA', 'QA Testing', 'Retest'];
        $blockedStatuses = ['QA Failed', 'Rejected'];

        $isCompleted = in_array($statusName, $completedStatuses);
        $isOverdue = $ticket->due_date && $ticket->due_date->isPast() && !$isCompleted;

        // Completion: 100% if done, otherwise based on logged hours
        $pComp = $isCompleted ? 100 : min(round($ticket->completudePercentage, 0), 100);

        // Bar color based on status
        if ($isCompleted) {
            $pClass = 'gtaskgreen';
        } elseif ($isOverdue || in_array($statusName, $blockedStatuses)) {
            $pClass = 'gtaskred';
        } elseif (in_array($statusName, $qaStatuses)) {
            $pClass = 'gtaskpurple';
        } elseif (in_array($statusName, $inProgressStatuses)) {
            $pClass = 'gtaskblue';
        } else {
            $pClass = 'g-custom-task';
        }

        return [
            "pID" => ($epic?->id ?? "N") . $ticket->id,
            "pName" => $ticket->name,
            "pStart" => "",
            "pEnd" => $ticket->due_date ? $ticket->due_date->format('Y-m-d') . " 23:59:59" : "",
            "pClass" => $pClass,
            "pLink" => "",
            "pMile" => 0,
            "pRes" => $ticket->responsible?->name ?? "",
            "pComp" => $pComp,
            "pGroup" => 0,
            "pParent" => $epic?->id ?? "",
            "pOpen" => 1,
            "pDepend" => "",
            "pCaption" => "",
            "pNotes" => $isOverdue ? "OVERDUE" : "",
            "pBarText" => $statusName,
            "meta" => [
                "id" => $ticket->id,
                "epic" => false,
                "parent" => $epic?->id ?? null,
                "slug" => $ticket->code,
                "due_date" => $ticket->due_date?->format('Y-m-d'),
                "is_overdue" => $isOverdue,
                "status" => $statusName
            ]
        ];
    }

}
