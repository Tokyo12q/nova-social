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
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'is_private' => (bool) $this->is_private,
            'is_active' => (bool) $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'profile' => new ProfileResource($this->whenLoaded('profile', fn () => $this->profile)),
            'followers_count' => $this->when(
                isset($this->followers_count),
                $this->followers_count,
                fn () => $this->relationLoaded('followers')
                    ? $this->followers->where('pivot.status', 'accepted')->count()
                    : $this->followers()->where('status', 'accepted')->count()
            ),
            'following_count' => $this->when(
                isset($this->following_count),
                $this->following_count,
                fn () => $this->relationLoaded('following')
                    ? $this->following->where('pivot.status', 'accepted')->count()
                    : $this->following()->where('status', 'accepted')->count()
            ),
        ];
    }
}
