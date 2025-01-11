<?php

use Dotenv\Dotenv;

class Database {
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;
    private ?PDO $conn = null;

    public function __construct() {
        // Load environment variables from .env file
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
        }

        // Get environment-specific configuration
        $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'development';

        $this->host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? ($env === 'production' ? 'prod' : 'hrms');
        $this->username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? ($env === 'production' ? 'prod-root' : 'root');
        $this->password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? ($env === 'production' ? '32o481ydhs8FDSf234' : '');
    }

    public function connection(): PDO {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("[DB Connection Error] " . $e->getMessage());

            // Display error details in development mode
            echo $this->humanReadableError($e->getMessage());
            exit;
        }

        return $this->conn;
    }

    private function humanReadableError(string $errorMessage): string {
        $env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'development';
        if ($env === 'development') {
            return "
                <h1>Database Connection Error</h1>
                <p><strong>Environment:</strong> $env</p>
                <p><strong>Database Name:</strong> {$this->db_name}</p>
                <p><strong>Host:</strong> {$this->host}</p>
                <p><strong>Error Message:</strong> $errorMessage</p>
            ";
        }

        return "An unexpected error occurred while connecting to the database. Please try again later.";
    }
}
