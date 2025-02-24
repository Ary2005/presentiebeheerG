<?php
// Database connection settings
$host = "localhost";
$dbname = "role_management_db";
$username = "root";
$password = "";


try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $new_password = $_POST['new_password'];

        // Check if the user exists
        $stmt = $pdo->prepare("SELECT ID FROM users WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update the password without hashing
            $update_stmt = $pdo->prepare("UPDATE users SET Password = :password WHERE Email = :email");
            $update_stmt->bindParam(':password', $new_password);
            $update_stmt->bindParam(':email', $email);

            if ($update_stmt->execute()) {
                
                echo "Password Resetted Successfully!";
            } else {
                echo "Error resetting password.";
            }
        } else {
            echo "No user found with this email.";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
