<?php
/**
 * FILE: config/database.php
 * DESCRIPTION: Database connection class for establishing and handling PDO connections.
 * 
 * USAGE:
 * Include this file and instantiate the `database` class to get a PDO connection:
 * Example:
 *   require __DIR__ . '/config/database.php';
 *   $db = new database();
 *   $pdo = $db->connection();
 * 
 * Handles connection errors gracefully with user-friendly error messages
 * and logs detailed exceptions for debugging purposes.
 * 
 * AUTHOR: Anthony Hudson / DFR Group LLC
 * CREATED: 2025-01-04
 * UPDATED: 2025-01-04
 */

class database {
    // Database credentials
    private $host = "localhost";
    private $db_name = "hrms";
    private $username = "root";
    private $password = "";
    public $conn;

    /**
     * Establish a database connection.
     * 
     * @return PDO|null Returns a PDO instance or null on failure.
     */
    public function connection() {
        $this->conn = null;

        try {
            // Data Source Name (DSN)
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Fetch associative arrays by default
                PDO::ATTR_EMULATE_PREPARES   => false,                 // Use native prepared statements
            ];

            // Create a new PDO instance
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

        } catch (PDOException $e) {
            // Log the detailed error for debugging
            error_log("[DB Error] " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());

            // Display a user-friendly message
            echo $this->humanReadableError($e);
            exit;
        }

        return $this->conn;
    }

    /**
     * Converts PDOException error codes into user-friendly messages.
     * 
     * @param PDOException $e The PDOException instance.
     * @return string A user-friendly error message.
     */
    private function humanReadableError(PDOException $e): string {
        $errorCode = $e->getCode();

        switch ($errorCode) {
            // Connection Errors
            case 1045:
                return "Invalid username or password for the database.";
            case 1049:
                return "The specified database does not exist. Please check your configuration.";
            case 2002:
                return "Could not connect to the database server. Ensure that the MySQL server is running.";
            case 1044:
                return "Access denied for the specified database. Check the database user permissions.";

            // Query Errors
            case 1064:
                return "There is a syntax error in the SQL query. Please contact support.";
            case 1062:
                return "Duplicate entry found. The data you're trying to add already exists.";
            case 1146:
                return "The specified database table does not exist. Please contact the administrator.";

            // Default case for unhandled errors
            default:
                return "An unexpected database error occurred: " . htmlspecialchars($e->getMessage());
        }
    }
}
