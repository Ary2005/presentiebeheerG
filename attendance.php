<?php
// attendance.php
session_start();
require 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];

if (!isset($_GET['assignment_id'])) {
    die("Assignment not specified.");
}
$assignmentID = intval($_GET['assignment_id']);

// Verify that the assignment belongs to this teacher and get class info
$sql = "SELECT ta.AssignmentID, s.SubjectName, c.ClassName, c.ClassID
        FROM teacher_assignments ta
        JOIN subjects s ON ta.SubjectID = s.SubjectID
        JOIN classes c ON ta.ClassID = c.ClassID
        WHERE ta.AssignmentID = ? AND ta.TeacherID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assignmentID, $userID);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    die("Assignment not found or you don't have permission.");
}
$assignment = $result->fetch_assoc();
$subjectName = $assignment['SubjectName'];
$className   = $assignment['ClassName'];
$classID     = $assignment['ClassID'];

// Set the attendance date (default to today's date)
$attendanceDate = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

// Get the list of students in the class (include UserID from the student table)
$sql2 = "SELECT StudID, UserID, Name, email FROM student WHERE ClassID = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $classID);
$stmt2->execute();
$studentsResult = $stmt2->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attendance for <?php echo htmlspecialchars($className); ?></title>
    <style>
        table { border-collapse: collapse; width: 80%; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($subjectName); ?> - <?php echo htmlspecialchars($className); ?></h1>
    <h2>Attendance for <?php echo htmlspecialchars($attendanceDate); ?></h2>
    <form method="post" action="process_attendance.php">
        <!-- Pass assignment ID and attendance date via hidden inputs -->
        <input type="hidden" name="assignment_id" value="<?php echo $assignmentID; ?>">
        <input type="hidden" name="attendance_date" value="<?php echo $attendanceDate; ?>">
        <table>
            <tr>
                <th>Student Name</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
            <?php while ($student = $studentsResult->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['Name']); ?></td>
                <td><?php echo htmlspecialchars($student['email']); ?></td>
                <td>
                    <!-- Use the student's UserID (not StudID) as the key -->
                    <select name="attendance[<?php echo $student['UserID']; ?>]">
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                        <option value="Excused">Excused</option>
                    </select>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <input type="submit" value="Submit Attendance">
    </form>
    <br>
    <a href="teacher_dashboard.php">Back to Dashboard</a>
</body>
</html>
<?php
$stmt->close();
$stmt2->close();
$conn->close();
?>
