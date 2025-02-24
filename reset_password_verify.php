<?php
session_start();

// Check that the required session variables exist
if (!isset($_SESSION['reset_email'], $_SESSION['otp'], $_SESSION['otp_expires'])) {
    echo "Session expired. Please start the password reset process again.";
    exit;
}

// Check if the OTP has expired
if (time() > $_SESSION['otp_expires']) {
    unset($_SESSION['otp'], $_SESSION['otp_expires']);
    echo "OTP has expired. Please start the password reset process again.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = $_POST['otp'];
    $new_password = $_POST['new_password'];

    // Verify the OTP
    if ($entered_otp == $_SESSION['otp']) {
        // OTP is correct—update the password
        $host = "localhost";
        $dbname = "role_management_db";
        $username = "root";
        $password = "";

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $email = $_SESSION['reset_email'];
            $update_stmt = $pdo->prepare("UPDATE users SET Password = :password WHERE Email = :email");
            $update_stmt->bindParam(':password', $new_password);  // Consider hashing in production!
            $update_stmt->bindParam(':email', $email);

            if ($update_stmt->execute()) {
                echo "Password Reset Successfully!";
            } else {
                echo "Error resetting password.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

        // Clear the OTP session variables
        unset($_SESSION['reset_email'], $_SESSION['otp'], $_SESSION['otp_expires']);
    } else {
        echo "Invalid OTP. Please try again.";
    }
}
?>
