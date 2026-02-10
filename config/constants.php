<?php 
// Start Session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Create Constants to Store Non Repeating Values
define('SITEURL', 'http://localhost/restaurant/'); // Update this if needed
define('LOCALHOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'restaurant');

// Database Connection
$conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
