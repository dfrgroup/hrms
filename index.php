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

// 5. Routing Logic (Simple Example)
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

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
        require __DIR__ . '/src/Views/login.view.php';
        break;

    case '/dashboard':
        require __DIR__ . '/src/Views/dashboard.view.php';
        break;

    default:
        // 404 Handler
        http_response_code(404);
        echo "404 - Page Not Found.";
        break;
}
