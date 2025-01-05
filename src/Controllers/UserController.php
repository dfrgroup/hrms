<?php

namespace App\Controllers;

use App\Models\User;

class UserController
{
    private User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Get all users with linked employee details.
     *
     * @return array List of users.
     */
    public function getAllUsers(): array
    {
        return $this->userModel->getAllUsers();
    }
}
