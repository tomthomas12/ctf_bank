<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'ctf_user');
define('DB_PASS', 'ctf_password');
define('DB_NAME', 'wolfcore_bank');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
