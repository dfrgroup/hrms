<?php

namespace App\Models;

use PDO;
use PDOException;

/**
 * Class User
 * Responsible for user-related database operations.
 */
class User
{
    /**
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Constructor.
     *
     * @param PDO $pdo An established PDO database connection.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Retrieve a user record by email.
     *
     * @param string $email The email to search for.
     * @return array|null An associative array of user data or null if none found.
     * @throws PDOException If a database error occurs.
     */
    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, email, password, created_at
              FROM users
             WHERE email = :email
        ");

        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function findUserById(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                users.id AS user_id,
                users.email,
                employees.first_name,
                employees.middle_name,
                employees.last_name
            FROM users
            LEFT JOIN employees ON users.id = employees.user_id
            WHERE users.id = :user_id
        ");

        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
    /**
     * Create a new user in the database.
     *
     * @param string $email    User’s email address (unique).
     * @param string $password Plaintext password to be hashed.
     * @return int The newly inserted user’s ID.
     * @throws PDOException If a database error occurs (e.g., duplicate email).
     */
    public function createUser(string $email, string $password): int
    {
        // Hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->pdo->prepare("
            INSERT INTO users (email, password)
            VALUES (:email, :password)
        ");

        $stmt->execute([
            'email'    => $email,
            'password' => $hashedPassword,
        ]);

        // Return the ID of the newly created user
        return (int)$this->pdo->lastInsertId();
    }

     /**
     * Get all users with linked employee details.
     *
     * @return array List of users and their linked employee info.
     */
    public function getAllUsers(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                users.id,
                users.email,
                employees.first_name,
                employees.last_name
            FROM users
            LEFT JOIN employees ON users.id = employees.user_id
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
