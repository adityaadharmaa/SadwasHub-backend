<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserProfile;

class UserProfilePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewKtp(User $user, UserProfile $profile): bool
    {
        return $user->hasRole('admin') || $user->id === $profile->user_id;
    }
}
