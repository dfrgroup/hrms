<?php

namespace App\Helpers;

use App\Models\User;

class UserHelper
{
    /**
     * Fetch the logged-in user's details based on session user_id.
     *
     * @param User $userModel The User model instance.
     * @return array|null The user details or null if not logged in.
     */
    public static function getLoggedInUserDetails(User $userModel): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        return $userModel->findUserById($_SESSION['user_id']);
    }
}
