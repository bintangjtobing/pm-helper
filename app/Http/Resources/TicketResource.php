<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'content' => $this->content,
            'order' => $this->order,
            'estimation' => $this->estimation,
            'due_date' => $this->due_date?->toDateString(),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'owner' => new UserResource($this->whenLoaded('owner')),
            'responsible' => new UserResource($this->whenLoaded('responsible')),
            'status' => $this->whenLoaded('status', fn() => $this->status ? [
                'id' => $this->status->id,
                'name' => $this->status->name,
                'color' => $this->status->color,
            ] : null),
            'type' => $this->whenLoaded('type', fn() => $this->type ? [
                'id' => $this->type->id,
                'name' => $this->type->name,
                'color' => $this->type->color,
                'icon' => $this->type->icon,
            ] : null),
            'priority' => $this->whenLoaded('priority', fn() => $this->priority ? [
                'id' => $this->priority->id,
                'name' => $this->priority->name,
                'color' => $this->priority->color,
            ] : null),
            'epic' => $this->whenLoaded('epic', fn() => $this->epic ? [
                'id' => $this->epic->id,
                'name' => $this->epic->name,
            ] : null),
            'total_logged_hours' => $this->when(
                $this->relationLoaded('hours'),
                fn() => $this->totalLoggedHours,
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
