<?php
// Simple Database Configuration
$host = 'localhost';        // Database host (usually localhost)
$username = 'adnan';         // Your database username
$password = 'Adnan@66202';             // Your database password
$database = 'syngo';        // Your database name

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
