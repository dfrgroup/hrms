<?php

namespace App\Controllers;

use App\Models\User;
use PDOException;

/**
 * Class LoginController
 * Handles user login logic.
 */
class LoginController
{
    /**
     * @var User
     */
    private User $userModel;

    /**
     * Constructor.
     *
     * @param User $userModel The User model instance for DB operations.
     */
    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Attempt to log a user in based on email and password.
     *
     * @param string $email    The user’s email address.
     * @param string $password The user’s plaintext password.
     * @return array An associative array with "success" (bool) and "message" (string).
     */
    public function login(string $email, string $password): array
    {
        try {
            $user = $this->userModel->findUserByEmail($email);
        } catch (PDOException $e) {
            // Log the error or handle it as needed.
            return ['success' => false, 'message' => 'Database error occurred'];
        }

        // 1. User not found
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // 2. Validate password against the stored hash
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid credentials'];
        }

        // 3. All good—user is authenticated
        return ['success' => true, 'message' => 'Login successful'];
    }
}
