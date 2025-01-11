<?php
class Database {
    private string $host;
    private string $db_name;
    private string $username;
    private string $password;
    private ?PDO $conn = null;

    public function __construct() {
        // Load environment-specific configuration
        $env = $this->getEnvironment();

        if ($env === 'production') {
            $this->host = "localhost";
            $this->db_name = "prod";
            $this->username = "prod-root";
            $this->password = "32o481ydhs8FDSf234";
        } else { // Default to development
            $this->host = "localhost";
            $this->db_name = "hrms";
            $this->username = "root";
            $this->password = "";
        }
    }

    public function connection(): PDO {
        if ($this->conn !== null) {
            return $this->conn;
        }

        try {
            // Data Source Name (DSN)
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exceptions
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Log the detailed error for debugging
            error_log("[DB Connection Error] " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

            // Display a user-friendly message with debugging information
            echo $this->humanReadableError($e->getMessage());
            exit;
        }

        return $this->conn;
    }

    private function getEnvironment(): string {
        return getenv('APP_ENV') ?: 'development';
    }

    private function humanReadableError(string $errorMessage): string {
        $env = $this->getEnvironment();
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
