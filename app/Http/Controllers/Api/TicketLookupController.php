<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketLookupController extends Controller
{
    public function statuses(Request $request): JsonResponse
    {
        $query = TicketStatus::query();
        if ($projectId = $request->get('project_id')) {
            $query->where(fn($q) => $q
                ->whereNull('project_id')
                ->orWhere('project_id', $projectId));
        } else {
            $query->whereNull('project_id');
        }

        $statuses = $query->orderBy('order')->get()->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'color' => $s->color,
            'order' => $s->order,
            'is_default' => (bool) $s->is_default,
        ]);

        return response()->json(['data' => $statuses]);
    }

    public function types(): JsonResponse
    {
        $types = TicketType::orderBy('name')->get()->map(fn($t) => [
            'id' => $t->id,
            'name' => $t->name,
            'color' => $t->color,
            'icon' => $t->icon,
            'is_default' => (bool) $t->is_default,
        ]);

        return response()->json(['data' => $types]);
    }

    public function priorities(): JsonResponse
    {
        $priorities = TicketPriority::orderBy('name')->get()->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'color' => $p->color,
            'is_default' => (bool) $p->is_default,
        ]);

        return response()->json(['data' => $priorities]);
    }
}
