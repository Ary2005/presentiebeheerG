<?php
// process_attendance.php
session_start();
require 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_POST['assignment_id'], $_POST['attendance_date'], $_POST['cohort'], $_POST['period'], $_POST['attendance'], $_POST['lesuur'])) {
    die("Ongeldige inzending.");
}

$assignmentID   = intval($_POST['assignment_id']);
$attendanceDate = $_POST['attendance_date'];
$cohortID       = intval($_POST['cohort']);
$periodID       = intval($_POST['period']);
$attendanceData = $_POST['attendance']; // array: key = student UserID, value = status
$lesuurData     = $_POST['lesuur'];     // array: key = student UserID, value = LesuurID

// Extra validatie: haal ClassID en SubjectID op van de teacher assignment
$sql = "SELECT ClassID, SubjectID FROM teacher_assignments WHERE AssignmentID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assignmentID);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $classID   = $row['ClassID'];
    $subjectID = $row['SubjectID'];
} else {
    die("Ongeldig assignment id.");
}
$stmt->close();

// Start de transactie
$conn->begin_transaction();

// Prepare statement voor de attendance-tabel
$attendanceSql = "INSERT INTO attendance (TeacherAssignmentID, UserID, LesuurID, AttendanceDate, CohortID, PeriodID, Status)
                  VALUES (?, ?, ?, ?, ?, ?, ?)
                  ON DUPLICATE KEY UPDATE Status = ?";
$stmtAttend = $conn->prepare($attendanceSql);
if (!$stmtAttend) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

// Loop door iedere inzending (per student)
foreach ($attendanceData as $userID => $status) {
    $userID = intval($userID);
    if (!isset($lesuurData[$userID])) {
        // Als er voor deze student geen lesuur is geselecteerd, sla deze over
        continue;
    }
    $selectedLesuur = intval($lesuurData[$userID]);
    
    if ($status === "Vrijstelling") {
        // Sla de vrijstelling op in de vrijstellingstabel
        $reason = ""; // Eventueel kun je hier een reden invullen
        $exemptionSql = "INSERT INTO vrijstelling (TeacherAssignmentID, UserID, LesuurID, VrijstellingsDatum, Reason)
                         VALUES (?, ?, ?, ?, ?)
                         ON DUPLICATE KEY UPDATE Reason = ?";
        $stmtExempt = $conn->prepare($exemptionSql);
        if (!$stmtExempt) {
            $conn->rollback();
            die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmtExempt->bind_param("iiisss", $assignmentID, $userID, $selectedLesuur, $attendanceDate, $reason, $reason);
        if (!$stmtExempt->execute()) {
            $conn->rollback();
            die("Fout bij invoegen van vrijstelling voor gebruiker ID $userID: " . $stmtExempt->error);
        }
        $stmtExempt->close();
    } else {
        // Sla de aanwezigheid op in de attendance-tabel met cohort en periode
        if (!$stmtAttend->bind_param("iiisisss", $assignmentID, $userID, $selectedLesuur, $attendanceDate, $cohortID, $periodID, $status, $status)) {
            $conn->rollback();
            die("Bind fout: " . $stmtAttend->error);
        }
        if (!$stmtAttend->execute()) {
            $conn->rollback();
            die("Fout bij opslaan van aanwezigheid voor gebruiker ID $userID: " . $stmtAttend->error);
        }
    }
}

// Commit de transactie
$conn->commit();
$stmtAttend->close();
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Presentie Verwerkt</title>
</head>
<body>
    <h1>Presentie succesvol opgeslagen!</h1>
    <p><a href="teacher_dashboard.php">Terug naar Dashboard</a></p>
</body>
</html>
