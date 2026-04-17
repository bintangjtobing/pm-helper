<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'ticket_prefix' => $this->ticket_prefix,
            'type' => $this->type,
            'status_type' => $this->status_type,
            'owner' => new UserResource($this->whenLoaded('owner')),
            'status' => $this->whenLoaded('status', fn() => [
                'id' => $this->status?->id,
                'name' => $this->status?->name,
                'color' => $this->status?->color,
            ]),
            'tickets_count' => $this->when(
                isset($this->tickets_count),
                $this->tickets_count
            ),
            'members_count' => $this->when(
                isset($this->users_count),
                $this->users_count
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
