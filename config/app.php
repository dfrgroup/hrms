<?php
/**
 * FILE: config/app.php
 * DESCRIPTION: Core application configuration file. Defines global settings
 * such as app name, environment, debug mode, and timezone.
 * 
 * USAGE:
 * Include this file wherever global app settings are needed.
 * Example: `$appConfig = require __DIR__ . '/config/app.php';`
 * 
 * AUTHOR: Anthony Hudson / DFR Group LLC
 * CREATED: 2025-01-04
 */

// Detect the current environment
$environment = ($_SERVER['HTTP_HOST'] === '127.0.0.1' || $_SERVER['HTTP_HOST'] === 'localhost') 
    ? 'development' 
    : 'production';

// Dynamically set the base URL
$base_url = ($environment === 'development') 
    ? 'http://127.0.0.1' 
    : 'https://taskvera.com';

return [
    // General Application Info
    'name' => 'HRMS',        // Application Name
    'env' => $environment,   // Environment: 'development', 'production', etc.
    'debug' => $environment === 'development', // Debug Mode: true in development, false in production
    'timezone' => 'UTC',     // Timezone for the application
    'base_url' => $base_url, // Dynamically set base URL

    // Database Error Handling (custom logic can be added later)
    'database_error' => function ($e) use ($environment) {
        // Log the exact error for debugging purposes
        error_log("[DB Error] " . $e->getMessage());

        // Friendly user-facing error
        return $environment === 'development'
            ? "Detailed Debug Info: " . htmlspecialchars($e->getMessage())
            : "A database error occurred. Please contact support.";
    },
];
