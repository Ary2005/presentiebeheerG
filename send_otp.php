<?php
session_start();

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

        // Check if the user exists
        $stmt = $pdo->prepare("SELECT ID FROM users WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Generate a 6-digit OTP
            $otp = rand(100000, 999999);

            // Store the OTP and email in session with an expiration time (5 minutes)
            $_SESSION['reset_email'] = $email;
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_expires'] = time() + 300; // 300 seconds = 5 minutes

            // Send the OTP to the user's email
            $subject = "Your Password Reset OTP";
            $message = "Your OTP for password reset is: $otp. It is valid for 5 minutes.";
            $headers = "From: no-reply@example.com";

            // In a real-world scenario, you should check the result of mail() and handle errors.
            mail($email, $subject, $message, $headers);

            // Redirect to the OTP verification page
            header("Location: reset_password_otp.php");
            exit;
        } else {
            echo "No user found with this email.";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
