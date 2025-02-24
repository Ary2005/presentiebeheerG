<?php
// process_attendance.php
session_start();
require 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['assignment_id'], $_POST['attendance_date'], $_POST['attendance'])) {
    die("Invalid submission.");
}

$assignmentID   = intval($_POST['assignment_id']);
$attendanceDate = $_POST['attendance_date'];
$attendanceData = $_POST['attendance'];

// ---------------------------------------------------------
// Step 1: Retrieve ClassID and SubjectID for the assignment
// ---------------------------------------------------------
$stmt = $conn->prepare("SELECT ClassID, SubjectID FROM teacher_assignments WHERE AssignmentID = ?");
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("i", $assignmentID);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $classID   = $row['ClassID'];
    $subjectID = $row['SubjectID'];
} else {
    die("Invalid assignment id.");
}
$stmt->close();

// ---------------------------------------------------------
// Step 2: Prepare statement for inserting/updating attendance
// ---------------------------------------------------------
$sql = "INSERT INTO student_attendance (UserID, ClassID, SubjectID, AttendanceDate, Status)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE Status = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

// ---------------------------------------------------------
// Step 3: Loop through attendance data and update records
// ---------------------------------------------------------
foreach ($attendanceData as $userID => $status) {
    $userID = intval($userID);
    $stmt->bind_param("iiisss", $userID, $classID, $subjectID, $attendanceDate, $status, $status);
    if (!$stmt->execute()) {
        echo "Error updating attendance for user ID $userID: " . $stmt->error . "<br>";
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Attendance Processed</title>
</head>
<body>
    <h1>Attendance Recorded Successfully!</h1>
    <p><a href="teacher_dashboard.php">Return to Dashboard</a></p>
</body>
</html>
