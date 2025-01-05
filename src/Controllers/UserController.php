<?php

namespace App\Controllers;

use App\Models\User;

/**
 * Class UserController
 * Handles various user-related endpoints.
 */
class UserController
{
    private User $userModel;

    /**
     * @param User $userModel The user model instance.
     */
    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Get all users with linked employee details (or other fields).
     *
     * @return array List of users.
     */
    public function getAllUsers(): array
    {
        return $this->userModel->getAllUsers();
    }
}
