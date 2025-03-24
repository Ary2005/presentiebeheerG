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

// Verwerk inzending (presentie invoeren)
// (De invoerform is hier uitgezet, alleen de filter en overzicht worden getoond)
// ...

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

// Haal ook alle beschikbare perioden op voor de filter
$queryPeriods = "SELECT PeriodID, PeriodName FROM perioden ORDER BY PeriodID";
$resultPeriods = $conn->query($queryPeriods);

// Filteropties verwerken (via GET)
$subjectFilter = isset($_GET['subject_filter']) && $_GET['subject_filter'] !== "" ? intval($_GET['subject_filter']) : null;
$statusFilter  = isset($_GET['status_filter']) && $_GET['status_filter'] !== "" ? $_GET['status_filter'] : null;
$dateFilter    = isset($_GET['date_filter']) && $_GET['date_filter'] !== "" ? $_GET['date_filter'] : null;
$periodFilter  = isset($_GET['period_filter']) && $_GET['period_filter'] !== "" ? intval($_GET['period_filter']) : null;

// Bouw de WHERE-voorwaarden voor de query
$whereConditions = " WHERE a.UserID = ? "; // Altijd alleen eigen records
$bindTypes = "i";
$bindValues = [$userID];

if ($subjectFilter !== null) {
    $whereConditions .= " AND s.SubjectID = ? ";
    $bindTypes .= "i";
    $bindValues[] = $subjectFilter;
}
if ($statusFilter !== null) {
    $whereConditions .= " AND a.Status = ? ";
    $bindTypes .= "s";
    $bindValues[] = $statusFilter;
}
if ($dateFilter !== null) {
    $whereConditions .= " AND a.AttendanceDate = ? ";
    $bindTypes .= "s";
    $bindValues[] = $dateFilter;
}
if ($periodFilter !== null) {
    $whereConditions .= " AND a.PeriodID = ? ";
    $bindTypes .= "i";
    $bindValues[] = $periodFilter;
}

// Haal de al ingevoerde presentiegegevens op met de docentnaam en periode
$query = "SELECT a.AttendanceDate, a.Status, s.SubjectName, p.PeriodName, l.LesuurNummer, l.StartTijd, l.EindTijd, u.Name AS TeacherName
          FROM attendance a
          JOIN teacher_assignments ta ON a.TeacherAssignmentID = ta.AssignmentID
          JOIN subjects s ON ta.SubjectID = s.SubjectID
          JOIN perioden p ON a.PeriodID = p.PeriodID
          JOIN lesuren l ON a.LesuurID = l.LesuurID
          JOIN users u ON ta.TeacherID = u.ID"
          . $whereConditions . "
          ORDER BY a.AttendanceDate DESC, l.LesuurNummer, s.SubjectName";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
// Dynamisch binden
$stmt->bind_param($bindTypes, ...$bindValues);
$stmt->execute();
$resultAttendance = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - Presentie bijhouden</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        h1 {
            font-size: 2.5em;
            margin-top: 20px;
        }
        h2 {
            font-size: 1.8em;
            margin-bottom: 20px;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 8px;
        }
        .filter-form label {
            margin-right: 10px;
            font-weight: 600;
            color: #555;
        }
        .filter-form select, .filter-form input[type="date"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
            margin-bottom: 10px;
            flex: 1;
            min-width: 150px;
        }
        .filter-form input[type="submit"] {
            padding: 10px 20px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .filter-form input[type="submit"]:hover {
            background-color: #218838;
        }
        .filter-form a {
            padding: 10px 20px;
            background-color: #dc3545;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .filter-form a:hover {
            background-color: #c82333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .message {
            text-align: center;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 20px;
        }
        .logout {
            text-align: center;
            margin-top: 20px;
        }
        .logout a {
            color: #007bff;
            text-decoration: none;
            font-weight: 600;
        }
        .logout a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Student Portal</h1>
        <?php if (!empty($message)) { echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; } ?>

        <!-- Filterformulier -->
        <div class="filter-form">
            <form method="get" action="">
                <input type="hidden" name="dummy" value="1">
                
                <label for="subject_filter">Vak:</label>
                <select id="subject_filter" name="subject_filter">
                    <option value="">Alle vakken</option>
                    <?php
                    $stmtSubjects->data_seek(0);
                    while ($subject = $resultSubjects->fetch_assoc()):
                    ?>
                        <option value="<?php echo $subject['SubjectID']; ?>" <?php if ($subjectFilter === intval($subject['SubjectID'])) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($subject['SubjectName']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                
                <label for="status_filter">Status:</label>
                <select id="status_filter" name="status_filter">
                    <option value="">Alle statussen</option>
                    <option value="Aanwezig" <?php if ($statusFilter === "Aanwezig") echo 'selected'; ?>>Aanwezig</option>
                    <option value="Afwezig" <?php if ($statusFilter === "Afwezig") echo 'selected'; ?>>Afwezig</option>
                    <option value="Ziek" <?php if ($statusFilter === "Ziek") echo 'selected'; ?>>Ziek</option>
                    <option value="Laat" <?php if ($statusFilter === "Laat") echo 'selected'; ?>>Laat</option>
                    <option value="Vrijstelling" <?php if ($statusFilter === "Vrijstelling") echo 'selected'; ?>>Vrijstelling</option>
                </select>
                
                <label for="date_filter">Datum:</label>
                <input type="date" id="date_filter" name="date_filter" value="<?php echo ($dateFilter !== null ? htmlspecialchars($dateFilter) : ''); ?>">

                <label for="period_filter">Periode:</label>
                <select id="period_filter" name="period_filter">
                    <option value="">Alle perioden</option>
                    <?php while ($period = $resultPeriods->fetch_assoc()): ?>
                        <option value="<?php echo $period['PeriodID']; ?>" <?php if ($periodFilter === intval($period['PeriodID'])) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($period['PeriodName']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="submit" value="Filter">
                <a href="student_portal.php">Reset filters</a>
            </form>
        </div>

        <h2>Jouw Ingevoerde Presentiegegevens</h2>
        <table>
            <tr>
                <th>Datum</th>
                <th>Periode</th>
                <th>Vak</th>
                <th>Lesuur</th>
                <th>Tijd</th>
                <th>Status</th>
            </tr>
            <?php while ($row = $resultAttendance->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['AttendanceDate']); ?></td>
                <td><?php echo htmlspecialchars($row['PeriodName']); ?></td>
                <td><?php echo htmlspecialchars($row['SubjectName']); ?></td>
                <td>Lesuur <?php echo htmlspecialchars($row['LesuurNummer']); ?></td>
                <td><?php echo date("H:i", strtotime($row['StartTijd'])); ?> - <?php echo date("H:i", strtotime($row['EindTijd'])); ?></td>
                <td><?php echo htmlspecialchars($row['Status']); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>