<?php
// FILE #3: config/database.php (PDO Connection)

class database {
    private $host = "localhost";
    private $db_name = "hr";
    private $username = "root";
    private $password = "";
    public $conn;

    public function connection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

        } catch (PDOException $e) {
            // Log the full raw message for debugging
            error_log("[DB Error] " . $e->getMessage());

            // Display a user-friendly message
            echo $this->humanReadableError($e);
            exit;
        }

        return $this->conn;
    }

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

            // Fallback for any other error
            default:
                return "An unexpected database error occurred: " . htmlspecialchars($e->getMessage());
        }
    }
}
