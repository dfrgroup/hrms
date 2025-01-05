<?php
// public/index.php

// 1. Load config files
$appConfig = require __DIR__ . '/../config/app.php';
$dbConfig  = require __DIR__ . '/../config/database.php';

// 2. Initialize Database Connection (PDO example)
$dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    if ($appConfig['debug']) {
        die("DB Connection Failed: " . $e->getMessage());
    }
    die("DB Error. Contact Admin.");
}

// 3. Session Setup, etc.
session_start();

// 4. Basic router or direct includes
// For example, you can parse the URL and load a controller accordingly.
// Or simply show a home page if this is the default route.

echo "Welcome to HRMS!";
