<?php
// Application Configuration
$appConfig = require __DIR__ . '/config/app.php';

// Include Database and Models/Controllers
require __DIR__ . '/config/database.php';
require __DIR__ . '/src/Models/User.php';
require __DIR__ . '/src/Controllers/LoginController.php';
require __DIR__ . '/src/Controllers/UserController.php';
require __DIR__ . '/src/Helpers/UserHelper.php';

use App\Models\User;
use App\Controllers\LoginController;
use App\Controllers\UserController;
use App\Helpers\UserHelper;

try {
    // Database Connection
    $db = new database();
    $pdo = $db->connection();
} catch (Exception $e) {
    error_log("[Initialization Error] " . $e->getMessage());
    echo $appConfig['debug'] 
        ? "Initialization Error: " . htmlspecialchars($e->getMessage()) 
        : "The application encountered an issue during startup. Please contact support.";
    exit;
}

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize Models and Controllers
$userModel = new User($pdo);
$loginController = new LoginController($userModel);
$userController = new UserController($userModel);

// Fetch the Logged-In User Details
$loggedInUser = UserHelper::getLoggedInUserDetails($userModel);

// Routing Logic
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($requestUri) {
    case '/':
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        header('Location: /dashboard');
        exit;

    case '/login':
        if ($requestMethod === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $response = $loginController->login($email, $password);

            if ($response['success']) {
                $userData = $userModel->findUserByEmail($email);
                $_SESSION['user_id'] = $userData['id'] ?? null;
                header('Location: /dashboard');
                exit;
            } else {
                $_SESSION['login_error'] = $response['message'];
                header('Location: /login');
                exit;
            }
        } else {
            $errorMessage = $_SESSION['login_error'] ?? null;
            unset($_SESSION['login_error']);
            require __DIR__ . '/src/Views/login.view.php';
        }
        break;

    case '/dashboard':
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $userId = $_SESSION['user_id'];
        $userDetails = $userModel->findUserById($userId);

        if (!$userDetails) {
            echo "Error: User not found.";
            exit;
        }

        require __DIR__ . '/src/Views/dashboard.view.php';
        break;

    case '/all-users':
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $allUsers = $userController->getAllUsers();
        require __DIR__ . '/src/Views/Auth/allusers.view.php';
        break;

    default:
        http_response_code(404);
        echo "404 - Page Not Found.";
        break;
}
