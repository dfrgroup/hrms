<?php
/**
 * FILE: config/Database.php
 * DESCRIPTION: Database connection class for establishing and handling PDO connections.
 * 
 * USAGE:
 * Include this file and instantiate the `Database` class to get a PDO connection:
 * Example:
 *   require __DIR__ . '/config/Database.php';
 *   $db = new Database();
 *   $pdo = $db->getConnection();
 * 
 * Handles connection errors gracefully with user-friendly error messages
 * and logs detailed exceptions for debugging purposes.
 * 
 * AUTHOR: Anthony Hudson / DFR Group LLC
 * CREATED: 2025-01-04
 * UPDATED: 2025-01-04
 */

class Database {
    // Database credentials
    private string $host = "localhost";
    private string $db_name = "prod";
    private string $username = "prod-root";
    private string $password = "32o481ydhs8FDSf234";
    private ?PDO $conn = null;

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
                PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
            ];

            // Create a new PDO instance
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
     * Returns a user-friendly error message without exposing sensitive details.
     * 
     * @return string The error message.
     */
    private function humanReadableError(): string {
        // Generic error message to display to users
        return "An unexpected error occurred while connecting to the database. Please try again later.";
    }
}
