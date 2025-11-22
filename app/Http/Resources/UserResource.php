<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'status' => $this->status,
            'last_seen_at' => $this->last_seen_at,
            'online_status' => $this->getOnlineStatus(),
            'last_seen_text' => $this->getLastSeenText(),
            'last_seen_detailed' => $this->getLastSeenDetailed(),
            'user_type' => $this->user_type,
            'is_student' => $this->is_student,
            'is_faculty' => $this->is_faculty,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'details' => new UserDetailResource($this->whenLoaded('userDetail')),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->pluck('name');
            }),
            'permissions' => $this->whenLoaded('permissions', function () {
                return $this->getAllPermissions()->pluck('name');
            }),
        ];
    }
}