<?php
// director_portal.php
session_start();
require 'dbconnect.php'; // Connect to your presentiebeheer database

// Ensure that only a director (RoleID 4) can access this page.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 4) {
    die("Access denied. This page is for directors only.");
}

// Query to retrieve attendance records along with class, subject, and student info
$query = "SELECT 
             sa.AttendanceDate, 
             sa.Status, 
             c.ClassName, 
             s.SubjectName, 
             st.Name AS StudentName, 
             st.email AS StudentEmail
          FROM student_attendance sa
          JOIN classes c ON sa.ClassID = c.ClassID
          JOIN subjects s ON sa.SubjectID = s.SubjectID
          JOIN student st ON sa.UserID = st.UserID
          ORDER BY c.ClassName, st.Name, s.SubjectName, sa.AttendanceDate DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Director Portal - Attendance Overview</title>
    <style>
        body {
            font-family: Arial, sans-serif; 
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        table {
            border-collapse: collapse; 
            width: 100%; 
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #aaa; 
            padding: 8px; 
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        a {
            text-decoration: none; 
            color: #0066cc;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Attendance Overview</h1>
    <table>
        <tr>
            <th>Date</th>
            <th>Class</th>
            <th>Subject</th>
            <th>Student Name</th>
            <th>Student Email</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['AttendanceDate']); ?></td>
            <td><?php echo htmlspecialchars($row['ClassName']); ?></td>
            <td><?php echo htmlspecialchars($row['SubjectName']); ?></td>
            <td><?php echo htmlspecialchars($row['StudentName']); ?></td>
            <td><?php echo htmlspecialchars($row['StudentEmail']); ?></td>
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
