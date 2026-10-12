<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProfileService
{
    /**
     * Retrieve a user's profile by username, taking into account viewer relationship and blocks.
     *
     * @return array{user: User, can_view_full_profile: bool}
     */
    public function getByUsername(string $username, ?User $viewer): array
    {
        $user = User::where('username', $username)
            ->with('profile')
            ->first();

        if (! $user) {
            throw new NotFoundHttpException('Resource not found.');
        }

        // Hide profile if blocked by or blocking viewer
        if ($viewer && $user->hasBlockRelationshipWith($viewer)) {
            throw new NotFoundHttpException('Resource not found.');
        }

        $user->loadCount('posts');
        $user->loadCount(['followers' => fn ($query) => $query->where('follows.status', 'accepted')]);
        $user->loadCount(['following' => fn ($query) => $query->where('follows.status', 'accepted')]);

        $canViewFull = $user->canViewFullProfile($viewer);

        return [
            'user' => $user,
            'can_view_full_profile' => $canViewFull,
        ];
    }

    /**
     * Update the authenticated user's profile and handle file uploads with deletion of old assets.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateProfile(
        User $user,
        array $data,
        ?UploadedFile $avatar = null,
        ?UploadedFile $cover = null
    ): User {
        return DB::transaction(function () use ($user, $data, $avatar, $cover) {
            // Update name on User model if provided
            if (isset($data['name'])) {
                $user->name = $data['name'];
                $user->save();
            }

            $profile = $user->profile ?? $user->profile()->create([]);

            // Handle avatar replacement & deletion of previous file
            if ($avatar) {
                $this->deleteStoredFile($profile->avatar_url);
                $avatarPath = $avatar->store('avatars', 'public');
                $profile->avatar_url = Storage::disk('public')->url($avatarPath);
            }

            // Handle cover replacement & deletion of previous file
            if ($cover) {
                $this->deleteStoredFile($profile->cover_url);
                $coverPath = $cover->store('covers', 'public');
                $profile->cover_url = Storage::disk('public')->url($coverPath);
            }

            // Update profile metadata
            if (array_key_exists('bio', $data)) {
                $profile->bio = $data['bio'];
            }
            if (array_key_exists('website', $data)) {
                $profile->website = $data['website'];
            }
            if (array_key_exists('location', $data)) {
                $profile->location = $data['location'];
            }

            $profile->save();

            $user->load('profile');
            $user->loadCount('posts');
            $user->loadCount(['followers' => fn ($query) => $query->where('follows.status', 'accepted')]);
            $user->loadCount(['following' => fn ($query) => $query->where('follows.status', 'accepted')]);

            return $user;
        });
    }

    /**
     * Delete an existing file from the public storage disk if it was stored locally.
     */
    public function deleteStoredFile(?string $url): void
    {
        if (empty($url)) {
            return;
        }

        $path = null;

        if (str_contains($url, '/storage/')) {
            $path = Str::after($url, '/storage/');
        } elseif (str_starts_with($url, 'storage/')) {
            $path = Str::after($url, 'storage/');
        } elseif (str_starts_with($url, 'avatars/') || str_starts_with($url, 'covers/')) {
            $path = $url;
        }

        if ($path) {
            $path = ltrim($path, '/');
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
