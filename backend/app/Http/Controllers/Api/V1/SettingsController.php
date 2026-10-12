<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateAccountSettingsRequest;
use App\Http\Resources\UserResource;
use App\Services\SettingsService;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsService $settingsService
    ) {}

    /**
     * Update account settings including privacy, email, and password.
     */
    public function update(UpdateAccountSettingsRequest $request): UserResource
    {
        $user = $request->user();

        Gate::authorize('updateSettings', $user);

        $updatedUser = $this->settingsService->updateSettings($user, $request->validated());

        return new UserResource($updatedUser);
    }

    /**
     * Toggle account privacy setting.
     */
    public function updatePrivacy(UpdateAccountSettingsRequest $request): UserResource
    {
        $user = $request->user();

        Gate::authorize('updateSettings', $user);

        $updatedUser = $this->settingsService->updatePrivacy(
            $user,
            (bool) $request->validated('is_private')
        );

        return new UserResource($updatedUser);
    }
}
