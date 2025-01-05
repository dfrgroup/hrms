<?php

namespace Tests\Auth;

use PHPUnit\Framework\TestCase;
use App\Controllers\LoginController;
use App\Models\User;
use PDO;

class UserLoginTest extends TestCase
{
    private $pdo;
    private $loginController;

    protected function setUp(): void
    {
        // Connect to a real MySQL database instead of in-memory SQLite
        // Update host, dbname, username, and password as needed
        $this->pdo = new PDO('mysql:host=127.0.0.1;dbname=hr', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Make sure the table exists. In a real test environment, you might want to recreate or clean up.
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        $this->pdo->exec("
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL
            ) ENGINE=InnoDB
        ");

        // Seed the database with a test user
        $this->pdo->exec("
            INSERT INTO users (email, password) VALUES (
                'user@example.com',
                '" . password_hash('secret123', PASSWORD_BCRYPT) . "'
            )
        ");

        // Inject the PDO instance into the User model
        $userModel = new User($this->pdo);

        // Instantiate the LoginController with the User model
        $this->loginController = new LoginController($userModel);
    }

    public function testLoginWithValidCredentials(): void
    {
        $email = 'user@example.com';
        $password = 'secret123';

        $result = $this->loginController->login($email, $password);

        $this->assertTrue($result['success']);
        $this->assertEquals('Login successful', $result['message']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $email = 'user@example.com';
        $password = 'wrongpassword';

        $result = $this->loginController->login($email, $password);

        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid credentials', $result['message']);
    }

    public function testLoginWithNonExistentUser(): void
    {
        $email = 'nonexistent@example.com';
        $password = 'password123';

        $result = $this->loginController->login($email, $password);

        $this->assertFalse($result['success']);
        $this->assertEquals('User not found', $result['message']);
    }
}
