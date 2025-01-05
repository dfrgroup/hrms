<?php
/**
 * FILE: index.php
 * DESCRIPTION: Main entry point for the HRMS application. Handles configuration loading, 
 * database initialization, session setup, and routing logic.
 *
 * USAGE:
 * - This file is the front controller and should be the only publicly accessible PHP file.
 * - Automatically loads required configurations and services.
 * 
 * AUTHOR: Anthony Hudson / DFR Group LLC
 * CREATED: 2025-01-04
 * UPDATED: 2025-01-04
 */

// 1. Load application configuration
$appConfig = require __DIR__ . '/config/app.php';

// 2. Include the database class
require __DIR__ . '/config/database.php';

// 2a. Include/autoload your Model and Controller classes
require __DIR__ . '/src/Models/User.php';
require __DIR__ . '/src/Controllers/LoginController.php';

use App\Models\User;
use App\Controllers\LoginController;

try {
    // 3. Instantiate the database and establish a PDO connection
    $db = new database();
    $pdo = $db->connection();
} catch (Exception $e) {
    // Log any unexpected errors during initialization
    error_log("[Initialization Error] " . $e->getMessage());

    // Display a user-friendly message
    echo $appConfig['debug'] 
        ? "Initialization Error: " . htmlspecialchars($e->getMessage()) 
        : "The application encountered an issue during startup. Please contact support.";
    exit;
}

// 4. Session Setup
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 4a. Create User and LoginController instances
$userModel = new User($pdo);
$loginController = new LoginController($userModel);

// 5. Routing Logic (Simple Example)
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($requestUri) {
    case '/':
        // Check if user_id is set in the session (indicating a logged-in user)
        if (empty($_SESSION['user_id'])) {
            // If not logged in, redirect to login
            header('Location: /login');
            exit;
        }
        // If already logged in, redirect to dashboard
        header('Location: /dashboard');
        exit;

        case '/login':
            if ($requestMethod === 'POST') {
                // Handle the POST request: attempt to log the user in
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
    
                $response = $loginController->login($email, $password);
    
                if ($response['success']) {
                    // Log user in (store in session)
                    $userData = $userModel->findUserByEmail($email);
                    $_SESSION['user_id'] = $userData['id'] ?? null;
    
                    header('Location: /dashboard');
                    exit;
                } else {
                    // Store error in session, then redirect
                    $_SESSION['login_error'] = $response['message'];
                    header('Location: /login');
                    exit;
                }
            } else {
                // GET request → show the login form
                // 1) Grab error message from session
                $errorMessage = $_SESSION['login_error'] ?? null;
                unset($_SESSION['login_error']); // clear it
    
                // 2) Render the login view, which checks $errorMessage
                require __DIR__ . '/src/Views/login.view.php';
            }
            break;

    case '/dashboard':
        // Simple check
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        require __DIR__ . '/src/Views/dashboard.view.php';
        break;

    default:
        // 404 Handler
        http_response_code(404);
        echo "404 - Page Not Found.";
        break;
}
