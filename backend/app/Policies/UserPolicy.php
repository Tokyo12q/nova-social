<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the target user.
     */
    public function view(User $user, User $targetUser): bool
    {
        return ! $targetUser->hasBlockRelationshipWith($user);
    }

    /**
     * Determine whether the user can update account settings.
     */
    public function updateSettings(User $user, User $targetUser): bool
    {
        return (int) $user->id === (int) $targetUser->id;
    }
}
