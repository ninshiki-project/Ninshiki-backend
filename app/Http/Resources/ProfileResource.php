<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'avatar' => $this->avatar,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department,
            'email_verified_at' => $this->email_verified_at,
            'roles_permission' => RolesResource::collection($this->roles),
            'notification' => $this->notifications,
        ];
    }
}