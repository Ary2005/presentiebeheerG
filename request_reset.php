<?php
// Database connection settings
$host = "localhost";
$dbname = "role_management_db3";
$username = "root";
$password = "";

try {
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
            // Generate a secure token (or OTP)
            $token = bin2hex(random_bytes(16)); // 32 characters
            // Set token expiration to 1 hour from now
            $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Insert token into the password_reset table
            $insert_stmt = $pdo->prepare("INSERT INTO password_reset (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
            $insert_stmt->bindParam(':user_id', $user['ID']);
            $insert_stmt->bindParam(':token', $token);
            $insert_stmt->bindParam(':expires_at', $expires);
            $insert_stmt->execute();

            // Build the password reset link (change the domain to yours)
            $reset_link = "https://yourdomain.com/reset_password.php?token=" . $token;
            $subject = "Password Reset Request";
            $message = "Click the following link to reset your password:\n\n" . $reset_link;
            $headers = "From: no-reply@yourdomain.com";

            if (mail($email, $subject, $message, $headers)) {
                echo "A password reset link has been sent to your email.";
            } else {
                echo "Error sending the email.";
            }
        } else {
            echo "No user found with this email.";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<html>
<head>
    <title>Request Password Reset</title>
</head>
<body>
    <h1>Request Password Reset</h1>
    <form method="post" action="">
        <label>Email:</label>
        <input type="email" name="email" required>
        <br><br>
        <input type="submit" value="Send Reset Link">
    </form>
</body>
</html>
