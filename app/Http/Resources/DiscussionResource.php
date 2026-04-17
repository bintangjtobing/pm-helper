<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscussionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'priority' => $this->priority,
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'ticket' => $this->whenLoaded('ticket', fn() => $this->ticket ? [
                'id' => $this->ticket->id,
                'code' => $this->ticket->code,
                'name' => $this->ticket->name,
            ] : null),
            'resolved_by' => new UserResource($this->whenLoaded('resolvedByUser')),
            'replies' => DiscussionReplyResource::collection($this->whenLoaded('replies')),
            'replies_count' => $this->when(
                isset($this->replies_count),
                $this->replies_count
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
