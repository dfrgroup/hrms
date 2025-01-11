<?php
/**
 * FILE: config/Database.php
 * DESCRIPTION: Database connection class for establishing and handling PDO connections.
 * Dynamically switches between development and production environments.
 */

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

    /**
     * Establish a database connection.
     *
     * @return PDO The PDO instance.
     * @throws Exception If connection fails.
     */
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

            // Display a user-friendly message without exposing sensitive details
            echo $this->humanReadableError();
            exit;
        }

        return $this->conn;
    }

    /**
     * Get the current environment.
     *
     * @return string 'production', 'development', etc.
     */
    private function getEnvironment(): string {
        // Set the environment manually or fetch from an environment variable
        // Example: Set in .htaccess or use getenv()
        return getenv('APP_ENV') ?: 'development';
    }

    /**
     * Returns a user-friendly error message without exposing sensitive details.
     *
     * @return string The error message.
     */
    private function humanReadableError(): string {
        return "An unexpected error occurred while connecting to the database. Please try again later.";
    }
}
