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

return [
    // General Application Info
    'name' => 'HRMS',        // Application Name
    'env' => 'development',  // Environment: 'development', 'production', 'staging', etc.
    'debug' => true,         // Debug Mode: true = show detailed errors, false = user-friendly errors
    'timezone' => 'UTC',     // Timezone for the application

    // Database Error Handling (custom logic can be added later)
    'database_error' => function ($e) {
        // Log the exact error for debugging purposes
        error_log("[DB Error] " . $e->getMessage());

        // Friendly user-facing error
        return (self::getConfig('debug'))
            ? "Detailed Debug Info: " . htmlspecialchars($e->getMessage())
            : "A database error occurred. Please contact support.";
    },
];
