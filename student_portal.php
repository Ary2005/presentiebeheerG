<?php
// student_portal.php
session_start();
require 'dbconnect.php'; // Zorg dat dit bestand de verbinding met de database regelt

// Controleer of de gebruiker is ingelogd en dat de rol beschikbaar is
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

// Alleen studenten (RoleID 3) hebben toegang
if ($_SESSION['user_role'] != 3) {
    die("Access denied. This portal is for students only.");
}

$userID = $_SESSION['user_id'];
$message = '';

// Verwerk inzending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_attendance'])) {
    $attendanceDate = $_POST['attendance_date']; // Verwacht Y-m-d
    $subjectID      = intval($_POST['subject_id']);
    $lesuurID       = intval($_POST['lesuur_id']);
    $status         = $_POST['status'];
    
    // Haal de klas op van de student
    $stmtStudent = $conn->prepare("SELECT ClassID FROM student WHERE UserID = ?");
    $stmtStudent->bind_param("i", $userID);
    $stmtStudent->execute();
    $resultStudent = $stmtStudent->get_result();
    if ($row = $resultStudent->fetch_assoc()) {
        $classID = $row['ClassID'];
    } else {
        die("Student record not found.");
    }
    $stmtStudent->close();
    
    // Zoek de bijbehorende teacher_assignment voor deze klas en dit vak
    $stmtTA = $conn->prepare("SELECT AssignmentID FROM teacher_assignments WHERE ClassID = ? AND SubjectID = ? LIMIT 1");
    $stmtTA->bind_param("ii", $classID, $subjectID);
    $stmtTA->execute();
    $resultTA = $stmtTA->get_result();
    if ($row = $resultTA->fetch_assoc()) {
        $teacherAssignmentID = $row['AssignmentID'];
    } else {
        die("Geen docent gevonden voor dit vak en deze klas.");
    }
    $stmtTA->close();
    
    // Insert of update in de attendance tabel
    $stmtAttend = $conn->prepare("INSERT INTO attendance (TeacherAssignmentID, UserID, LesuurID, AttendanceDate, Status)
                                  VALUES (?, ?, ?, ?, ?)
                                  ON DUPLICATE KEY UPDATE Status = ?");
    if (!$stmtAttend) {
        die("Prepare failed: " . $conn->error);
    }
    $stmtAttend->bind_param("iiisss", $teacherAssignmentID, $userID, $lesuurID, $attendanceDate, $status, $status);
    if ($stmtAttend->execute()) {
        $message = "Presentie succesvol opgeslagen!";
    } else {
        $message = "Er is een fout opgetreden: " . $stmtAttend->error;
    }
    $stmtAttend->close();
}

// Haal de klas van de student op (om de vakken te bepalen)
$stmtStudent = $conn->prepare("SELECT ClassID FROM student WHERE UserID = ?");
$stmtStudent->bind_param("i", $userID);
$stmtStudent->execute();
$resultStudent = $stmtStudent->get_result();
if ($row = $resultStudent->fetch_assoc()) {
    $classID = $row['ClassID'];
} else {
    die("Student record not found.");
}
$stmtStudent->close();

// Haal de vakken op voor de klas (via teacher_assignments)
$querySubjects = "SELECT DISTINCT s.SubjectID, s.SubjectName
                  FROM teacher_assignments ta
                  JOIN subjects s ON ta.SubjectID = s.SubjectID
                  WHERE ta.ClassID = ?";
$stmtSubjects = $conn->prepare($querySubjects);
$stmtSubjects->bind_param("i", $classID);
$stmtSubjects->execute();
$resultSubjects = $stmtSubjects->get_result();

// Haal de beschikbare lesuren op
$queryLesuren = "SELECT LesuurID, LesuurNummer, StartTijd, EindTijd FROM lesuren ORDER BY LesuurNummer";
$resultLesuren = $conn->query($queryLesuren);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Portal - Presentie bijhouden</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { text-align: center; }
        .form-container { width: 90%; margin: 20px auto; border: 1px solid #aaa; padding: 15px; }
        .form-container label { display: block; margin-bottom: 5px; }
        .form-container input, .form-container select { width: 100%; padding: 8px; margin-bottom: 10px; }
        .radio-group { margin-bottom: 10px; }
        .radio-group label { display: block; margin-bottom: 5px; }
        table { border-collapse: collapse; width: 90%; margin: 20px auto; }
        th, td { border: 1px solid #aaa; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { text-align: center; font-weight: bold; color: green; }
        a { text-decoration: none; color: #0066cc; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>Student Portal</h1>
    <!--<h2>Jouw Presentie Invoeren</h2>
    <?php if (!empty($message)) { echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; } ?>
    <div class="form-container">
        <form method="post" action="">
            <label for="attendance_date">Datum:</label>
            <input type="date" id="attendance_date" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
            
            <label for="subject_id">Vak:</label>
            <select id="subject_id" name="subject_id" required>
                <option value="">-- Selecteer een vak --</option>
                <?php while ($subject = $resultSubjects->fetch_assoc()): ?>
                    <option value="<?php echo $subject['SubjectID']; ?>">
                        <?php echo htmlspecialchars($subject['SubjectName']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <label>Lesuur:</label>
            <?php if ($resultLesuren->num_rows > 0): ?>
                <div class="radio-group">
                <?php while ($lesuur = $resultLesuren->fetch_assoc()): ?>
                    <label>
                        <input type="radio" name="lesuur_id" value="<?php echo $lesuur['LesuurID']; ?>" required>
                        Lesuur <?php echo $lesuur['LesuurNummer']; ?> (<?php echo date("H:i", strtotime($lesuur['StartTijd'])); ?> - <?php echo date("H:i", strtotime($lesuur['EindTijd'])); ?>)
                    </label>
                <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>Geen lesuren beschikbaar.</p>
            <?php endif; ?>
            
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="Aanwezig">Aanwezig</option>
                <option value="Afwezig">Afwezig</option>
                <option value="Ziek">Ziek</option>
                <option value="Laat">Laat</option>
                <option value="Vrijstelling">Vrijstelling</option>
            </select>
            
            <input type="submit" name="submit_attendance" value="Presentie Opslaan">
        </form>
    </div>-->
    
    <h2>Jouw Ingevoerde Presentiegegevens</h2>
    <table>
        <tr>
            <th>Datum</th>
            <th>Vak</th>
            <th>Lesuur</th>
            <th>Tijd</th>
            <th>Status</th>
            
        </tr>
        <?php
        // Haal de al ingevoerde presentiegegevens op met de docentnaam
        $query = "SELECT a.AttendanceDate, a.Status, s.SubjectName, l.LesuurNummer, l.StartTijd, l.EindTijd, u.Name AS TeacherName
                  FROM attendance a
                  JOIN teacher_assignments ta ON a.TeacherAssignmentID = ta.AssignmentID
                  JOIN subjects s ON ta.SubjectID = s.SubjectID
                  JOIN lesuren l ON a.LesuurID = l.LesuurID
                  JOIN users u ON ta.TeacherID = u.ID
                  WHERE a.UserID = ?
                  ORDER BY a.AttendanceDate DESC, l.LesuurNummer, s.SubjectName";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $resultAttendance = $stmt->get_result();
        while ($row = $resultAttendance->fetch_assoc()):
        ?>
        <tr>
            <td><?php echo htmlspecialchars($row['AttendanceDate']); ?></td>
            <td><?php echo htmlspecialchars($row['SubjectName']); ?></td>
            <td>Lesuur <?php echo htmlspecialchars($row['LesuurNummer']); ?></td>
            <td><?php echo date("H:i", strtotime($row['StartTijd'])); ?> - <?php echo date("H:i", strtotime($row['EindTijd'])); ?></td>
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
