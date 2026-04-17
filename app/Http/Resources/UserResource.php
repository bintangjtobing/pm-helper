<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'status' => $this->effectiveStatus(),
            'status_message' => $this->status_message,
            'department' => $this->whenLoaded('department', fn() => [
                'id' => $this->department?->id,
                'name' => $this->department?->name,
            ]),
            'position' => $this->whenLoaded('position', fn() => [
                'id' => $this->position?->id,
                'name' => $this->position?->name,
            ]),
        ];
    }
}
