<?php
session_start();
require 'dbconnect.php'; // Ensure this connects to the `presentiebeheer` database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Define roles and their respective dashboards
        $roles = [
            'admin' => ['table' => 'admin', 'dashboard' => 'admin_dashboard.php'],
            //'director' => ['table' => 'Director', 'dashboard' => 'admin_dashboard.php'],
        ];

        $foundUser = false;

        // Check the `users` table for roles rc, teacher, student
        $query = "SELECT ID, Name, Password, RoleID FROM users WHERE Email = ?";
        $stmt = mysqli_prepare($conn, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);

                // Compare hashed passwords
                if (password_verify($password, $row['Password'])) {
                    $_SESSION['user_id'] = $row['ID'];
                    $_SESSION['user_name'] = $row['Name'];
                    $_SESSION['user_role'] = $row['RoleID'];

                    // Redirect based on the role
                    switch ($row['RoleID']) {
                        case '5':
                            header("Location: director_portal.php");
                            break;
                        case '2':
                            header("Location: dashboard.php");
                            break;
                        case '3':
                            header("Location: student_portal.php");
                            break;
                        case '4':
                            header("Location: director_portal.php");
                            break;
                        case '1':
                            header("Location: beheerGebruikers.php");
                            break;
                        default:
                            $_SESSION['error'] = "Invalid role.";
                            header("Location: home.html");
                            break;
                    }
                    exit();
                } else {
                    $_SESSION['error'] = "Invalid email or password.";
                    header("Location: home.html");
                    exit();
                }
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "Database query failed: " . mysqli_error($conn);
            exit();
        }

        // Check the `admin` and `director` tables
        foreach ($roles as $role => $details) {
            $query = "SELECT ID, Name, Password FROM " . $details['table'] . " WHERE Email = ?";
            $stmt = mysqli_prepare($conn, $query);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) === 1) {
                    $row = mysqli_fetch_assoc($result);

                    if (password_verify($password, $row['Password'])) {
                        $_SESSION['user_id'] = $row['ID'];
                        $_SESSION['user_name'] = $row['Name'];
                        $_SESSION['user_role'] = $role;

                        header("Location: " . $details['dashboard']);
                        exit();
                    } else {
                        $_SESSION['error'] = "Invalid email or password.";
                        header("Location: home.html");
                        exit();
                    }
                }

                mysqli_stmt_close($stmt);
            } else {
                echo "Database query failed: " . mysqli_error($conn);
                exit();
            }
        }

        // If no user was found
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: home.html");
        exit();
    } else {
        $_SESSION['error'] = "Please provide both email and password.";
        header("Location: home.html");
        exit();
    }
} else {
    header("Location: home.html");
    exit();
}
?>

