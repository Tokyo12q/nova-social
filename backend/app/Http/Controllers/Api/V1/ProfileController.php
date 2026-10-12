<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserProfileResource;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProfileController extends Controller
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    /**
     * View a user profile by username.
     */
    public function show(Request $request, string $username): UserProfileResource
    {
        $viewer = $request->user('sanctum') ?? $request->user();

        $result = $this->profileService->getByUsername($username, $viewer);

        return new UserProfileResource($result['user'], $result['can_view_full_profile']);
    }

    /**
     * Update the authenticated user's profile and upload avatar/cover.
     */
    public function update(UpdateProfileRequest $request): UserProfileResource
    {
        $user = $request->user();
        $profile = $user->profile ?? $user->profile()->create([]);

        Gate::authorize('update', $profile);

        $cover = $request->file('cover') ?? $request->file('cover_image');

        $updatedUser = $this->profileService->updateProfile(
            $user,
            $request->validated(),
            $request->file('avatar'),
            $cover
        );

        return new UserProfileResource($updatedUser, true);
    }
}
