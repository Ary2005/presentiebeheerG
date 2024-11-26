<?php
session_start();
require 'dbconnect.php'; // Ensure this file connects to the `presentiebeheer` database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query to check if the user exists in the admin table
    $query = "SELECT ID, Name, Password, Permissions FROM admin WHERE Email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verify password (assuming passwords are not hashed in the current setup)
        if ($password === $row['Password']) {
            // Store admin info in session
            $_SESSION['admin_id'] = $row['ID'];
            $_SESSION['admin_name'] = $row['Name'];
            $_SESSION['admin_permissions'] = $row['Permissions'];

            // Redirect to admin dashboard
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Invalid password
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: home.html");
            exit();
        }
    } else {
        // Admin not found
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: home.html");
        exit();
    }
} else {
    header("Location: home.html");
    exit();
}
?>
