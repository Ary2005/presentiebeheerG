<?php
// student_portal.php
session_start();
require 'dbconnect.php'; // This should connect to your presentiebeheer database

// Check if the user is logged in and has a role set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

// Check if the user is a student (RoleID 3 as set in your login code)
if ($_SESSION['user_role'] != 3) {
    die("Access denied. This portal is for students only.");
}

$userID = $_SESSION['user_id'];

// Retrieve attendance records for the student by joining student_attendance, classes, and subjects
$query = "SELECT sa.AttendanceDate, sa.Status, c.ClassName, s.SubjectName
          FROM student_attendance sa
          JOIN classes c ON sa.ClassID = c.ClassID
          JOIN subjects s ON sa.SubjectID = s.SubjectID
          WHERE sa.UserID = ?
          ORDER BY sa.AttendanceDate DESC, c.ClassName, s.SubjectName";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Portal - Attendance</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        table { border-collapse: collapse; width: 90%; margin: 20px auto; }
        th, td { border: 1px solid #aaa; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        a { text-decoration: none; color: #0066cc; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Your Attendance Records</h1>
    <table>
        <tr>
            <th>Date</th>
            <th>Class</th>
            <th>Subject</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['AttendanceDate']); ?></td>
            <td><?php echo htmlspecialchars($row['ClassName']); ?></td>
            <td><?php echo htmlspecialchars($row['SubjectName']); ?></td>
            <td><?php echo htmlspecialchars($row['Status']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p style="text-align: center;"><a href="logout.php">Logout</a></p>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
