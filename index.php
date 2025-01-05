<?php
// public/index.php

// 1. Load config (if you have a separate app config)
$appConfig = require __DIR__ . '/config/app.php';

// 2. Require the database class
require __DIR__ . '/config/database.php';

// 3. Instantiate & get the PDO connection
$db = new database();
$pdo = $db->connection();

// 4. Session Setup
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// 4. Basic router or direct includes
// For example, you can parse the URL and load a controller accordingly.
// Or simply show a home page if this is the default route.

echo "Welcome to HRMS!";
