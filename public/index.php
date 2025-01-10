<?php
// Define base path
define('BASE_PATH', dirname(__DIR__));

// Application Configuration
$appConfig = require BASE_PATH . '/config/app.php';

// Include Database and Models/Controllers
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/src/Models/User.php';
require BASE_PATH . '/src/Controllers/LoginController.php';
require BASE_PATH . '/src/Controllers/UserController.php';
require BASE_PATH . '/src/Helpers/UserHelper.php';

use App\Models\User;
use App\Controllers\LoginController;
use App\Controllers\UserController;
use App\Helpers\UserHelper;

try {
    // Database Connection
    $db = new Database(); // Ensure the class name is correct
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
$userModel        = new User($pdo);
$loginController  = new LoginController($userModel);
$userController   = new UserController($userModel);

// Fetch the Logged-In User Details
$loggedInUser = UserHelper::getLoggedInUserDetails($userModel);

// Routing Logic
$requestUri    = $_SERVER['REQUEST_URI']    ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

switch ($requestUri) {
    case '/':
        // Redirect to /login if not authenticated, otherwise /dashboard
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        header('Location: /dashboard');
        exit;

    case '/login':
        // Handle login
        if ($requestMethod === 'POST') {
            $email    = $_POST['email']    ?? '';
            $password = $_POST['password'] ?? '';
            $response = $loginController->login($email, $password);

            if ($response['success']) {
                $userData             = $userModel->findUserByEmail($email);
                $_SESSION['user_id']  = $userData['id'] ?? null;
                header('Location: /dashboard');
                exit;
            } else {
                $_SESSION['login_error'] = $response['message'];
                header('Location: /login');
                exit;
            }
        } else {
            // Show the login form
            $errorMessage = $_SESSION['login_error'] ?? null;
            unset($_SESSION['login_error']);
            require BASE_PATH . '/src/Views/login.view.php';
        }
        break;

    case '/dashboard':
        // Must be logged in
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $userId      = $_SESSION['user_id'];
        $userDetails = $userModel->findUserById($userId);

        if (!$userDetails) {
            echo "Error: User not found.";
            exit;
        }
        require BASE_PATH . '/src/Views/dashboard.view.php';
        break;

    case '/all-users':
        // Must be logged in
        if (empty($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $allUsers = $userController->getAllUsers();
        require BASE_PATH . '/src/Views/Auth/allusers.view.php';
        break;

    default:
        // Check for routes like /edit-user/123
        if (preg_match('#^/edit-user/(\d+)$#', $requestUri, $matches)) {
            if (empty($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }
            
            $userId      = $matches[1];
            $userDetails = $userModel->findUserById($userId);
            if (!$userDetails) {
                echo "Error: User not found.";
                exit;
            }

            if ($requestMethod === 'POST') {
                // e.g. $userController->updateUser($userId, $_POST);
                // redirect or show success
            } else {
                // Show edit form
                require BASE_PATH . '/src/Views/Auth/edituser.view.php';
            }
        }
        // Check for routes like /Auth/ViewUser/123
        elseif (preg_match('#^/Auth/ViewUser/(\d+)$#', $requestUri, $matches)) {
            if (empty($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }
            
            $userId      = $matches[1];
            $userDetails = $userModel->findUserById($userId);
            if (!$userDetails) {
                echo "Error: User not found.";
                exit;
            }

            if ($requestMethod === 'POST') {
                // e.g. $userController->updateUser($userId, $_POST);
                // redirect or show success
            } else {
                // Show user details
                require BASE_PATH . '/src/Views/Auth/ViewUser.php';
            }
        }
        // Otherwise, 404
        else {
            http_response_code(404);
            echo "404 - Page Not Found.";
        }
        break;
}
