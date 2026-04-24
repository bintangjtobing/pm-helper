<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerFeedbackResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'change_type' => $this->change_type,
            'proposed_data' => $this->proposed_data,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'user' => new UserResource($this->whenLoaded('user')),
            'converted_ticket' => $this->whenLoaded('convertedTicket', fn () => $this->convertedTicket ? [
                'id' => $this->convertedTicket->id,
                'code' => $this->convertedTicket->code,
                'name' => $this->convertedTicket->name,
            ] : null),
            'comments' => CustomerFeedbackCommentResource::collection($this->whenLoaded('comments')),
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'activities' => CustomerFeedbackActivityResource::collection($this->whenLoaded('activities')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
