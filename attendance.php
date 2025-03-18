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
    die("Assignment niet gespecificeerd.");
}
$assignmentID = intval($_GET['assignment_id']);

// Haal op dat de opdracht bij de docent hoort en verkrijg klasinfo
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
    die("Assignment niet gevonden of u heeft geen toegang.");
}
$assignment = $result->fetch_assoc();
$subjectName = $assignment['SubjectName'];
$className   = $assignment['ClassName'];
$classID     = $assignment['ClassID'];

// Stel de datum in (standaard vandaag)
$attendanceDate = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

// Haal alle studenten in de klas op
$sql2 = "SELECT StudID, UserID, Name, email FROM student WHERE ClassID = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $classID);
$stmt2->execute();
$studentsResult = $stmt2->get_result();

// Haal de beschikbare lesuren op en sla ze op in een array
$sql3 = "SELECT LesuurID, LesuurNummer, StartTijd, EindTijd FROM lesuren ORDER BY LesuurNummer";
$resultLesuren = $conn->query($sql3);
$lesuren = [];
while($row = $resultLesuren->fetch_assoc()){
    $lesuren[] = $row;
}

// Haal beschikbare cohorten op
$sqlCohorts = "SELECT CohortID, SchoolYear FROM cohorts";
$resultCohorts = $conn->query($sqlCohorts);
$cohorts = [];
while($row = $resultCohorts->fetch_assoc()){
    $cohorts[] = $row;
}

// Haal beschikbare perioden op
$sqlPeriods = "SELECT PeriodID, PeriodName FROM perioden";
$resultPeriods = $conn->query($sqlPeriods);
$periods = [];
while($row = $resultPeriods->fetch_assoc()){
    $periods[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Presentie voor <?php echo htmlspecialchars($className); ?></title>
    <style>
        table { border-collapse: collapse; width: 90%; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: left; }
        .lesuur-options label { margin-right: 10px; display: block; }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($subjectName); ?> - <?php echo htmlspecialchars($className); ?></h1>
    <h2>Presentie voor <?php echo htmlspecialchars($attendanceDate); ?></h2>
    <form method="post" action="process_attendance.php">
        <!-- Verborgen velden met basisgegevens -->
        <input type="hidden" name="assignment_id" value="<?php echo $assignmentID; ?>">
        <input type="hidden" name="attendance_date" value="<?php echo $attendanceDate; ?>">
        
        <!-- Dropdown voor Cohort -->
        <label for="cohort">Kies Cohort:</label>
        <select name="cohort" id="cohort" required>
            <?php foreach($cohorts as $cohort): ?>
                <option value="<?php echo $cohort['CohortID']; ?>">
                    <?php echo htmlspecialchars($cohort['SchoolYear']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        <!-- Dropdown voor Periode -->
        <label for="period">Kies Periode:</label>
        <select name="period" id="period" required>
            <?php foreach($periods as $period): ?>
                <option value="<?php echo $period['PeriodID']; ?>">
                    <?php echo htmlspecialchars($period['PeriodName']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>
        
        <table>
            <tr>
                <th>Student Naam</th>
                <th>Email</th>
                <th>Lesuur</th>
                <th>Status</th>
            </tr>
            <?php while ($student = $studentsResult->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($student['Name']); ?></td>
                <td><?php echo htmlspecialchars($student['email']); ?></td>
                <td>
                    <div class="lesuur-options">
                        <?php foreach($lesuren as $lesuur): ?>
                            <label>
                                <input type="radio" name="lesuur[<?php echo $student['UserID']; ?>]" value="<?php echo $lesuur['LesuurID']; ?>" required>
                                Lesuur <?php echo $lesuur['LesuurNummer']; ?> 
                                (<?php echo date("H:i", strtotime($lesuur['StartTijd'])); ?> - <?php echo date("H:i", strtotime($lesuur['EindTijd'])); ?>)
                            </label>
                        <?php endforeach; ?>
                    </div>
                </td>
                <td>
                    <select name="attendance[<?php echo $student['UserID']; ?>]">
                        <option value="Aanwezig">Aanwezig</option>
                        <option value="Afwezig">Afwezig</option>
                        <option value="Ziek">Ziek</option>
                        <option value="Laat">Laat</option>
                        <option value="Vrijstelling">Vrijstelling</option>
                    </select>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <input type="submit" value="Presentie Opslaan">
    </form>
    <br>
    <a href="teacher_dashboard.php">Terug naar Dashboard</a>
</body>
</html>
<?php
$stmt->close();
$stmt2->close();
$conn->close();
?>
