<?php
// teacher_dashboard.php
session_start();
require 'dbconnect.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];

// Create database connection


// Query the assignments for the logged in teacher
$sql = "SELECT ta.AssignmentID, s.SubjectName, c.ClassName
        FROM teacher_assignments ta
        JOIN subjects s ON ta.SubjectID = s.SubjectID
        JOIN classes c ON ta.ClassID = c.ClassID
        WHERE ta.TeacherID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard</title>
    <style>
        table { border-collapse: collapse; width: 80%; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>Welcome, Teacher</h1>
    <h2>Your Assignments</h2>
    <table>
        <tr>
            <th>Subject</th>
            <th>Class</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['SubjectName']); ?></td>
            <td><?php echo htmlspecialchars($row['ClassName']); ?></td>
            <td>
                <!-- Link passes the assignment ID to the attendance page -->
                <a href="attendance.php?assignment_id=<?php echo $row['AssignmentID']; ?>">Take/View Attendance</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
