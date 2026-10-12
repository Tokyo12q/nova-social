<?php

namespace App\Services;

use App\Models\User;

class SettingsService
{
    /**
     * Toggle or update account privacy.
     */
    public function updatePrivacy(User $user, bool $isPrivate): User
    {
        $user->is_private = $isPrivate;
        $user->save();

        return $user->fresh()->load('profile');
    }

    /**
     * Update account settings including privacy, email, or password.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateSettings(User $user, array $data): User
    {
        if (array_key_exists('is_private', $data)) {
            $user->is_private = (bool) $data['is_private'];
        }

        if (! empty($data['email'])) {
            $user->email = $data['email'];
        }

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return $user->fresh()->load('profile');
    }
}
