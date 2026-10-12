<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>|null
     */
    public function toArray(Request $request): ?array
    {
        if (is_null($this->resource)) {
            return null;
        }

        return [
            'id' => $this->id,
            'bio' => $this->bio,
            'website' => $this->website,
            'avatar_url' => $this->avatar_url,
            'cover_url' => $this->cover_url,
            'location' => $this->location,
        ];
    }
}
