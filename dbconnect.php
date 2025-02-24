<?php
$host = "localhost"; // Change to your server address if needed
$dbname = "role_management_db";
$username = "root"; // Default username in XAMPP
$password = ""; // Default password in XAMPP

// Create the connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
