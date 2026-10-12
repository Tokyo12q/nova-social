<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Optional override for full profile visibility.
     */
    protected ?bool $canViewFullProfile;

    public function __construct($resource, ?bool $canViewFullProfile = null)
    {
        parent::__construct($resource);
        $this->canViewFullProfile = $canViewFullProfile;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $request->user('sanctum') ?? $request->user();

        $canViewFull = $this->canViewFullProfile ?? $this->resource->canViewFullProfile($viewer);

        $profile = $this->relationLoaded('profile') ? $this->profile : $this->profile()->first();

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'avatar_url' => $profile?->avatar_url,
            'is_private' => (bool) $this->is_private,
            'can_view_full_profile' => (bool) $canViewFull,
            'posts_count' => $this->when(
                isset($this->posts_count),
                $this->posts_count,
                fn () => $this->relationLoaded('posts') ? $this->posts->count() : $this->posts()->count()
            ),
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
            'joined_at' => $this->created_at?->toISOString(),
        ];

        if ($canViewFull) {
            $data['bio'] = $profile?->bio;
            $data['website'] = $profile?->website;
            $data['location'] = $profile?->location;
            $data['cover_url'] = $profile?->cover_url;
        } else {
            $data['bio'] = null;
            $data['website'] = null;
            $data['location'] = null;
            $data['cover_url'] = null;
        }

        return $data;
    }
}
