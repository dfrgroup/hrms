<?php

// Database configuration
$host = 'localhost'; // Replace with your database host
$dbname = 'hrms'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

try {
    // Connect to the database using PDO
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Connected to the database successfully.\n";

    // Create the users table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
    ");

    echo "Users table created or already exists.\n";

    // Insert a dummy user
    $email = 'admin@example.com'; // Replace with desired email
    $plainPassword = 'pass123';   // Replace with desired password
    $hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
    $stmt->execute([
        ':email' => $email,
        ':password' => $hashedPassword,
    ]);

    echo "Dummy user seeded successfully:\n";
    echo "Email: $email\n";
    echo "Password: $plainPassword (hashed as: $hashedPassword)\n";

} catch (PDOException $e) {
    // Handle any errors
    echo "Database error: " . $e->getMessage() . "\n";
    exit;
} catch (Exception $e) {
    // Handle other errors
    echo "Error: " . $e->getMessage() . "\n";
    exit;
}
