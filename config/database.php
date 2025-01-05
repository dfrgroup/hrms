<?php
// FILE #3: config/database.php (PDO Connection)

class database {
    private $host = "localhost";
    private $db_name = "hrms";
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
        }
        catch(PDOException $exception){
            error_log("Connection error: " . $exception->getMessage());
            die("Database connection error.");
        }
        return $this->conn;
    }
}
