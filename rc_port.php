<?php
// rc_portal.php
session_start();
require 'dbconnect.php'; // Zorg dat dit bestand de databaseverbinding regelt

// Toegangscontrole: alleen RC's (RoleID 5) mogen deze pagina zien
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 5) {
    die("Toegang geweigerd. Deze pagina is alleen voor RC's.");
}

// Haal de RC's toegewezen richting op (bijvoorbeeld uit de sessie of via een query)
$rcRichting = isset($_SESSION['user_richting']) ? $_SESSION['user_richting'] : ''; 
if (empty($rcRichting)) {
    die("Geen richting toegewezen aan deze RC.");
}

// Als er een specifieke klas is geselecteerd, tonen we het aanwezigheids-/presentieoverzicht
if (isset($_GET['class_id'])) {
    $classID = intval($_GET['class_id']);
    
    // Filterwaarden uit GET-parameters halen (voor aanwezigheidspagina)
    $studentFilter    = isset($_GET['student_filter']) && $_GET['student_filter'] !== "" ? intval($_GET['student_filter']) : null;
    $studentSearch    = isset($_GET['student_search']) && $_GET['student_search'] !== "" ? $_GET['student_search'] : null;
    $dateFilter       = isset($_GET['date_filter']) && $_GET['date_filter'] !== "" ? $_GET['date_filter'] : null;
    $cohortFilter     = isset($_GET['cohort_filter']) && $_GET['cohort_filter'] !== "" ? intval($_GET['cohort_filter']) : null;
    $periodFilter     = isset($_GET['period_filter']) && $_GET['period_filter'] !== "" ? intval($_GET['period_filter']) : null;
    $statusFilter     = isset($_GET['status_filter']) && $_GET['status_filter'] !== "" ? $_GET['status_filter'] : null;
    
    // Bouw de dynamische WHERE-voorwaarden op (we filter nu op klas en de extra filteropties)
    $conditions = ["c.ClassID = ?"];
    $types = "i";
    $params = [$classID];
    
    if ($studentFilter !== null) {
        $conditions[] = "st.UserID = ?";
        $types .= "i";
        $params[] = $studentFilter;
    }
    if ($studentSearch !== null) {
        $conditions[] = "st.Name LIKE ?";
        $types .= "s";
        $params[] = "%" . $studentSearch . "%";
    }
    if ($dateFilter !== null) {
        $conditions[] = "AttendanceDate = ?";
        $types .= "s";
        $params[] = $dateFilter;
    }
    if ($cohortFilter !== null) {
        $conditions[] = "co.CohortID = ?";
        $types .= "i";
        $params[] = $cohortFilter;
    }
    if ($periodFilter !== null) {
        $conditions[] = "p.PeriodID = ?";
        $types .= "i";
        $params[] = $periodFilter;
    }
    if ($statusFilter !== null) {
        $conditions[] = "Status = ?";
        $types .= "s";
        $params[] = $statusFilter;
    }
    
    // Bouw de UNION query op om zowel de reguliere aanwezigheid als vrijstellingen op te halen
    $query = "SELECT * FROM (
        SELECT 
             a.AttendanceDate,
             a.Status,
             c.ClassID,
             c.ClassName,
             s.SubjectID,
             s.SubjectName,
             st.UserID,
             st.Name AS StudentName,
             st.email AS StudentEmail,
             l.LesuurID,
             l.LesuurNummer,
             l.StartTijd,
             l.EindTijd,
             u.ID AS TeacherID,
             u.Name AS TeacherName,
             co.CohortID,
             co.SchoolYear,
             p.PeriodID,
             p.PeriodName
        FROM attendance a
        JOIN teacher_assignments ta ON a.TeacherAssignmentID = ta.AssignmentID
        JOIN classes c ON ta.ClassID = c.ClassID
        JOIN subjects s ON ta.SubjectID = s.SubjectID
        JOIN lesuren l ON a.LesuurID = l.LesuurID
        JOIN users u ON ta.TeacherID = u.ID
        JOIN student st ON a.UserID = st.UserID
        JOIN cohorts co ON a.CohortID = co.CohortID
        JOIN perioden p ON a.PeriodID = p.PeriodID
        WHERE c.ClassID = ?
        UNION ALL
        SELECT 
             v.VrijstellingsDatum AS AttendanceDate,
             'Vrijstelling' AS Status,
             c.ClassID,
             c.ClassName,
             s.SubjectID,
             s.SubjectName,
             st.UserID,
             st.Name AS StudentName,
             st.email AS StudentEmail,
             l.LesuurID,
             l.LesuurNummer,
             l.StartTijd,
             l.EindTijd,
             u.ID AS TeacherID,
             u.Name AS TeacherName,
             v.CohortID,
             co.SchoolYear,
             v.PeriodID,
             p.PeriodName
        FROM vrijstelling v
        JOIN teacher_assignments ta ON v.TeacherAssignmentID = ta.AssignmentID
        JOIN classes c ON ta.ClassID = c.ClassID
        JOIN subjects s ON ta.SubjectID = s.SubjectID
        JOIN lesuren l ON v.LesuurID = l.LesuurID
        JOIN users u ON ta.TeacherID = u.ID
        JOIN student st ON v.UserID = st.UserID
        LEFT JOIN cohorts co ON v.CohortID = co.CohortID
        LEFT JOIN perioden p ON v.PeriodID = p.PeriodID
        WHERE c.ClassID = ?
    ) AS combined";
    
    // Omdat in beide SELECT's de ClassID-voorwaarde voorkomt, voegen we deze twee keer toe
    // Voeg de dynamische filters toe
    if (count($conditions) > 1) { // eerste conditie is al "c.ClassID = ?"
        $query .= " AND " . implode(" AND ", array_slice($conditions, 1));
    }
    
    $query .= " ORDER BY AttendanceDate DESC, StudentName";
    
    // Bereid de query voor
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Voorbereiding mislukt: " . $conn->error);
    }
    // We binden eerst de ClassID twee keer (voor de twee SELECT's), gevolgd door de overige parameters
    $bindTypes = "ii" . substr($types, 1); // de eerste conditie "c.ClassID" komt twee keer
    $bindParams = [$classID, $classID];
    if (strlen($bindTypes) > 2) {
        $bindParams = array_merge($bindParams, array_slice($params, 1));
    }
    $stmt->bind_param($bindTypes, ...$bindParams);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Haal filteropties op voor de aanwezigheidspagina
    $studentOptions = [];
    $queryStudents = "SELECT st.UserID, st.Name FROM student st JOIN users u ON st.UserID = u.ID ORDER BY st.Name";
    $resultStudents = $conn->query($queryStudents);
    while ($row = $resultStudents->fetch_assoc()) {
        $studentOptions[] = $row;
    }
    
    $cohortOptions = [];
    $queryCohorts = "SELECT CohortID, SchoolYear FROM cohorts ORDER BY SchoolYear";
    $resultCohorts = $conn->query($queryCohorts);
    while ($row = $resultCohorts->fetch_assoc()) {
        $cohortOptions[] = $row;
    }
    
    $periodOptions = [];
    $queryPeriods = "SELECT PeriodID, PeriodName FROM perioden ORDER BY PeriodName";
    $resultPeriods = $conn->query($queryPeriods);
    while ($row = $resultPeriods->fetch_assoc()) {
        $periodOptions[] = $row;
    }
    ?>
    <!DOCTYPE html>
    <html lang="nl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>RC Portaal - Aanwezigheid bijhouden</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="#">RC Portaal</a>
                <div class="collapse navbar-collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="logout.php">Uitloggen</a></li>
                        <li class="nav-item"><a class="nav-link" href="rc_portal.php">Terug naar docentenlijst</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    
        <div class="container mt-4">
            <h2>Presentie overzicht voor klas: <?php 
                // Haal de klasnaam op
                $queryClass = $conn->prepare("SELECT ClassName FROM classes WHERE ClassID = ?");
                $queryClass->bind_param("i", $classID);
                $queryClass->execute();
                $queryClass->bind_result($className);
                $queryClass->fetch();
                echo htmlspecialchars($className);
                $queryClass->close();
            ?></h2>
    
            <!-- Filterformulier -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="class_id" value="<?php echo $classID; ?>">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="student_filter" class="form-label">Student:</label>
                                <select id="student_filter" name="student_filter" class="form-select">
                                    <option value="">Alle studenten</option>
                                    <?php foreach ($studentOptions as $option): ?>
                                        <option value="<?php echo $option['UserID']; ?>" <?php if ($studentFilter == $option['UserID']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($option['Name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_filter" class="form-label">Datum:</label>
                                <input type="date" id="date_filter" name="date_filter" class="form-control" value="<?php echo ($dateFilter !== null ? htmlspecialchars($dateFilter) : ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="cohort_filter" class="form-label">Cohort:</label>
                                <select id="cohort_filter" name="cohort_filter" class="form-select">
                                    <option value="">Alle cohort</option>
                                    <?php foreach ($cohortOptions as $option): ?>
                                        <option value="<?php echo $option['CohortID']; ?>" <?php if ($cohortFilter == $option['CohortID']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($option['SchoolYear']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="period_filter" class="form-label">Periode:</label>
                                <select id="period_filter" name="period_filter" class="form-select">
                                    <option value="">Alle perioden</option>
                                    <?php foreach ($periodOptions as $option): ?>
                                        <option value="<?php echo $option['PeriodID']; ?>" <?php if ($periodFilter == $option['PeriodID']) echo 'selected'; ?>>
                                            <?php echo htmlspecialchars($option['PeriodName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status_filter" class="form-label">Status:</label>
                                <select id="status_filter" name="status_filter" class="form-select">
                                    <option value="">Alle statussen</option>
                                    <option value="Aanwezig" <?php if ($statusFilter === "Aanwezig") echo 'selected'; ?>>Aanwezig</option>
                                    <option value="Afwezig" <?php if ($statusFilter === "Afwezig") echo 'selected'; ?>>Afwezig</option>
                                    <option value="Ziek" <?php if ($statusFilter === "Ziek") echo 'selected'; ?>>Ziek</option>
                                    <option value="Laat" <?php if ($statusFilter === "Laat") echo 'selected'; ?>>Laat</option>
                                    <option value="Vrijstelling" <?php if ($statusFilter === "Vrijstelling") echo 'selected'; ?>>Vrijstelling</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="student_search" class="form-label">Zoek student:</label>
                                <input type="text" id="student_search" name="student_search" class="form-control" placeholder="Naam invoeren" value="<?php echo ($studentSearch !== null ? htmlspecialchars($studentSearch) : ''); ?>">
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Filter toepassen</button>
                                <a href="rc_portal.php?class_id=<?php echo $classID; ?>" class="btn btn-secondary">Filters resetten</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
    
            <!-- Aanwezigheidstabel -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Aanwezigheidsgegevens</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Klas</th>
                                    <th>Vak</th>
                                    <th>Lesuur</th>
                                    <th>Tijd</th>
                                    <th>Student</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Schooljaar</th>
                                    <th>Periode</th>
                                    <th>Docent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['AttendanceDate']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ClassName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['SubjectName']); ?></td>
                                    <td>Lesuur <?php echo htmlspecialchars($row['LesuurNummer']); ?></td>
                                    <td><?php echo date("H:i", strtotime($row['StartTijd'])); ?> - <?php echo date("H:i", strtotime($row['EindTijd'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['StudentName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['StudentEmail']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['SchoolYear']); ?></td>
                                    <td><?php echo htmlspecialchars($row['PeriodName']); ?></td>
                                    <td><?php echo htmlspecialchars($row['TeacherName']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    
        <?php
        $stmt->close();
        ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    // Einde aanwezigheidsgedeelte
    exit;
}

// Als er geen klas is geselecteerd, tonen we eerst de docentenlijst met de bijbehorende klassen.
// Alleen docenten die behoren tot de RC's toegewezen richting worden getoond.
$query = "SELECT DISTINCT u.ID AS TeacherID, u.Name AS TeacherName, 
                 c.ClassID, c.ClassName
          FROM teacher t 
          JOIN users u ON t.UserID = u.ID 
          JOIN classes c ON t.ClassID = c.ClassID
          WHERE t.Richting = ?
          ORDER BY u.Name, c.ClassName";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Voorbereiding mislukt: " . $conn->error);
}
$stmt->bind_param("s", $rcRichting);
$stmt->execute();
$resultTeachers = $stmt->get_result();
$teachers = [];
while ($row = $resultTeachers->fetch_assoc()) {
    $teachers[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RC Portaal - Docenten & Klassen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">RC Portaal</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Uitloggen</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h2>Docenten en hun klassen voor richting: <?php echo htmlspecialchars($rcRichting); ?></h2>
        <?php if (empty($teachers)): ?>
            <p>Er zijn geen docenten gevonden voor deze richting.</p>
        <?php else: ?>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Docent</th>
                        <th>Klas</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $teacher): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($teacher['TeacherName']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['ClassName']); ?></td>
                        <td>
                            <!-- Link naar aanwezigheids-/presentiepagina voor deze klas -->
                            <a href="rc_portal.php?class_id=<?php echo $teacher['ClassID']; ?>" class="btn btn-primary btn-sm">Bekijk presentie</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>
