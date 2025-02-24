<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: home.html");
    exit();
}

// Display admin info
$admin_name = htmlspecialchars($_SESSION['admin_name']);
$permissions = htmlspecialchars($_SESSION['admin_permissions']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        /* General Styling */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f0f9ff, #cbebff);
            color: #333;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 1rem;
            text-align: center;
        }
        header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .container {
            margin: 2rem auto;
            padding: 1.5rem;
            max-width: 90%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        .info {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .info p {
            margin: 0.5rem 0;
        }
        .buttons {
            text-align: center;
            margin-bottom: 2rem;
        }
        .buttons a {
            text-decoration: none;
            margin: 0 1rem;
            padding: 0.5rem 1rem;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .buttons a:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #555;
        }
        /* Responsive Design */
        @media (max-width: 768px) {
            .buttons a {
                margin: 0.5rem 0;
                display: block;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
    </header>
    <div class="container">
        <!-- Alert Message -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert" id="error-alert">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); // Clear the error after displaying ?>
            </div>
        <?php endif; ?>

        <div class="info">
            <p><strong>Welcome, <?php echo $admin_name; ?></strong></p>
            <p>Permissions: <?php echo $permissions; ?></p>
        </div>
        <div class="buttons">
            <a href="add_user.php">Add New User</a>
            <a href="logout.php">Logout</a>
        </div>
        <h2>Existing Users</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
            <?php
            require 'dbconnect.php';
            $result = $conn->query("SELECT ID, Name, Email, Role FROM users");

            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Role']) . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
    <footer>
        <p>&copy; 2024 Admin Dashboard. All rights reserved.</p>
    </footer>
</body>
</html>
