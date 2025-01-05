<?php
/**
 * admin_register.php
 * A simple admin-only user registration page with dual creation for Users and Employees.
 */

session_start();

// Function to generate a UUID v4 string
function generateUUID() {
    // Generate 16 random bytes
    $data = random_bytes(16);

    // Set version to 0100
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

    // Output the 36-character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// Database credentials
$dbHost = 'localhost';    // Database host
$dbName = 'hrms';         // Database name
$dbUser = 'root';         // Database username
$dbPass = '';             // Database password

$message = "";

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $userType = $_POST['user_type'] ?? 'Employee'; // Default to 'Employee'
    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');

    // Basic validation
    if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        $message = "Error: Email, Password, First Name, and Last Name fields cannot be empty.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Error: Invalid email format.";
    } elseif (!in_array($userType, ['Admin', 'Manager', 'Employee', 'Guest'])) {
        $message = "Error: Invalid User Type selected.";
    } else {
        try {
            // Begin transaction
            $pdo->beginTransaction();

            // Generate a UUID v4 string
            $uuid = generateUUID();

            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the user into the Users table
            $stmtUser = $pdo->prepare("
                INSERT INTO Users (ID, Email, PasswordHash, UserType)
                VALUES (:id, :email, :passwordHash, :userType)
            ");
            $stmtUser->execute([
                ':id' => $uuid,
                ':email' => $email,
                ':passwordHash' => $hashedPassword,
                ':userType' => $userType
            ]);

            // Insert the employee into the employees table
            $stmtEmployee = $pdo->prepare("
                INSERT INTO employees (user_id, first_name, middle_name, last_name)
                VALUES (:user_id, :first_name, :middle_name, :last_name)
            ");
            $stmtEmployee->execute([
                ':user_id' => $uuid,
                ':first_name' => $firstName,
                ':middle_name' => $middleName !== '' ? $middleName : null,
                ':last_name' => $lastName
            ]);

            // Commit transaction
            $pdo->commit();

            $message = "Success: New user and employee created successfully.";
        } catch (PDOException $e) {
            // Rollback transaction on error
            $pdo->rollBack();

            // Handle potential errors (e.g., duplicate email)
            if ($e->getCode() == 23000) { // Integrity constraint violation
                $message = "Error: Email already exists.";
            } else {
                $message = "Database Error: " . htmlspecialchars($e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin - Register New User and Employee</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            padding: 20px 40px 40px 40px;
            border-radius: 8px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form div {
            margin-bottom: 15px;
        }
        label {
            display: block;
            color: #555;
            margin-bottom: 5px;
        }
        input[type="email"],
        input[type="password"],
        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4285f4;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #357ae8;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
        .error {
            background-color: #f8d7da;
            color: #a94442;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register a New User and Employee (Admin Only)</h1>

        <?php if ($message !== ""): ?>
            <div class="message <?php echo strpos($message, 'Success') === 0 ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <fieldset>
                <legend><strong>User Credentials</strong></legend>
                <div>
                    <label for="email">Email:</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        placeholder="user@example.com"
                        required
                    >
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        placeholder="Enter a strong password"
                        required
                    >
                </div>
                <div>
                    <label for="user_type">User Type:</label>
                    <select name="user_type" id="user_type" required>
                        <option value="Employee">Employee</option>
                        <option value="Manager">Manager</option>
                        <option value="Admin">Admin</option>
                        <option value="Guest">Guest</option>
                    </select>
                </div>
            </fieldset>
            <br>
            <fieldset>
                <legend><strong>Employee Details</strong></legend>
                <div>
                    <label for="first_name">First Name:</label>
                    <input 
                        type="text" 
                        name="first_name" 
                        id="first_name" 
                        placeholder="First Name"
                        required
                    >
                </div>
                <div>
                    <label for="middle_name">Middle Name:</label>
                    <input 
                        type="text" 
                        name="middle_name" 
                        id="middle_name" 
                        placeholder="Middle Name (Optional)"
                    >
                </div>
                <div>
                    <label for="last_name">Last Name:</label>
                    <input 
                        type="text" 
                        name="last_name" 
                        id="last_name" 
                        placeholder="Last Name"
                        required
                    >
                </div>
            </fieldset>
            <br>
            <input type="submit" value="Create User and Employee">
        </form>
    </div>
</body>
</html>
