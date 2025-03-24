<?php
// Database connection settings
$host = "localhost";
$dbname = "role_management_db3";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['token'])) {
        $token = $_GET['token'];

        // Verify that the token exists and is not expired
        $stmt = $pdo->prepare("SELECT pr.user_id, pr.expires_at, u.Email FROM password_reset pr JOIN users u ON pr.user_id = u.ID WHERE pr.token = :token");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data && strtotime($data['expires_at']) > time()) {
            // Token is valid
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                if ($new_password === $confirm_password) {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update the user's password
                    $update_stmt = $pdo->prepare("UPDATE users SET Password = :password WHERE ID = :user_id");
                    $update_stmt->bindParam(':password', $hashed_password);
                    $update_stmt->bindParam(':user_id', $data['user_id']);
                    $update_stmt->execute();

                    // Remove the token so it cannot be reused
                    $delete_stmt = $pdo->prepare("DELETE FROM password_reset WHERE token = :token");
                    $delete_stmt->bindParam(':token', $token);
                    $delete_stmt->execute();

                    echo "Password reset successfully!";
                } else {
                    echo "Passwords do not match.";
                }
            }
        } else {
            echo "Invalid or expired token.";
            exit;
        }
    } else {
        echo "No token provided.";
        exit;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h1>Reset Your Password</h1>
    <form method="post" action="">
        <label>New Password:</label>
        <input type="password" name="new_password" required>
        <br><br>
        <label>Confirm Password:</label>
        <input type="password" name="confirm_password" required>
        <br><br>
        <input type="submit" value="Reset Password">
    </form>
</body>
</html>
