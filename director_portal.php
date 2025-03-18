<?php
// director_portal.php
session_start();
require 'dbconnect.php'; // Zorg dat dit bestand de databaseverbinding regelt

// Toegangscontrole: alleen directeuren (RoleID 4) mogen deze pagina zien
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] != 4) {
    die("Toegang geweigerd. Deze pagina is alleen voor directeuren.");
}

// Filterwaarden uit GET-parameters halen
$classFilter   = isset($_GET['class_filter']) && $_GET['class_filter'] !== "" ? intval($_GET['class_filter']) : null;
$subjectFilter = isset($_GET['subject_filter']) && $_GET['subject_filter'] !== "" ? intval($_GET['subject_filter']) : null;
$studentFilter = isset($_GET['student_filter']) && $_GET['student_filter'] !== "" ? intval($_GET['student_filter']) : null;
$cohortFilter  = isset($_GET['cohort_filter']) && $_GET['cohort_filter'] !== "" ? intval($_GET['cohort_filter']) : null;
$periodFilter  = isset($_GET['period_filter']) && $_GET['period_filter'] !== "" ? intval($_GET['period_filter']) : null;
$statusFilter  = isset($_GET['status_filter']) && $_GET['status_filter'] !== "" ? $_GET['status_filter'] : null;

// Bouw de dynamische WHERE-voorwaarden op voor de gecombineerde query.
// De kolomnamen komen uit de UNION-subquery (alias 'combined').
$conditions = [];
$types = "";
$params = [];

if ($classFilter !== null) {
    $conditions[] = "ClassID = ?";
    $types .= "i";
    $params[] = $classFilter;
}
if ($subjectFilter !== null) {
    $conditions[] = "SubjectID = ?";
    $types .= "i";
    $params[] = $subjectFilter;
}
if ($studentFilter !== null) {
    $conditions[] = "UserID = ?";
    $types .= "i";
    $params[] = $studentFilter;
}
if ($cohortFilter !== null) {
    $conditions[] = "CohortID = ?";
    $types .= "i";
    $params[] = $cohortFilter;
}
if ($periodFilter !== null) {
    $conditions[] = "PeriodID = ?";
    $types .= "i";
    $params[] = $periodFilter;
}
if ($statusFilter !== null) {
    $conditions[] = "Status = ?";
    $types .= "s";
    $params[] = $statusFilter;
}

// Bouw de UNION query die zowel aanwezigheid als vrijstellingen ophaalt.
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
         NULL AS CohortID,
         '' AS SchoolYear,
         NULL AS PeriodID,
         '' AS PeriodName
    FROM vrijstelling v
    JOIN teacher_assignments ta ON v.TeacherAssignmentID = ta.AssignmentID
    JOIN classes c ON ta.ClassID = c.ClassID
    JOIN subjects s ON ta.SubjectID = s.SubjectID
    JOIN lesuren l ON v.LesuurID = l.LesuurID
    JOIN users u ON ta.TeacherID = u.ID
    JOIN student st ON v.UserID = st.UserID
) AS combined";

// Voeg de dynamische filters toe als er voorwaarden zijn
if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY ClassName, StudentName, SubjectName, AttendanceDate DESC";

// Bereid de query voor en voer deze uit
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Voorbereiding mislukt: " . $conn->error);
}
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Filteropties ophalen
$classOptions = [];
$queryClasses = "SELECT ClassID, ClassName FROM classes ORDER BY ClassName";
$resultClasses = $conn->query($queryClasses);
while ($row = $resultClasses->fetch_assoc()) {
    $classOptions[] = $row;
}

$subjectOptions = [];
$querySubjects = "SELECT SubjectID, SubjectName FROM subjects ORDER BY SubjectName";
$resultSubjects = $conn->query($querySubjects);
while ($row = $resultSubjects->fetch_assoc()) {
    $subjectOptions[] = $row;
}

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
    <title>Directeur Portaal - Aanwezigheidsoverzicht</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,.15);
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead th {
            background-color: #343a40;
            color: #fff;
            border-bottom: none;
        }
        .table tbody tr {
            transition: all 0.2s ease;
        }
        .table tbody tr:hover {
            background-color: #f1f3f5;
            transform: scale(1.01);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Navigatiebalk -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Directeur Portaal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Schakel navigatie">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Uitloggen</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Filterformulier -->
    <div class="container mt-4">
        <h1 class="text-center mb-4">Aanwezigheidsoverzicht</h1>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Filteropties</h5>
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-2">
                        <label for="class_filter" class="form-label">Klas:</label>
                        <select id="class_filter" name="class_filter" class="form-select">
                            <option value="">Alle klassen</option>
                            <?php foreach ($classOptions as $option): ?>
                                <option value="<?php echo $option['ClassID']; ?>" <?php if ($classFilter == $option['ClassID']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($option['ClassName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="subject_filter" class="form-label">Vak:</label>
                        <select id="subject_filter" name="subject_filter" class="form-select">
                            <option value="">Alle vakken</option>
                            <?php foreach ($subjectOptions as $option): ?>
                                <option value="<?php echo $option['SubjectID']; ?>" <?php if ($subjectFilter == $option['SubjectID']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($option['SubjectName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <label for="cohort_filter" class="form-label">Schooljaar:</label>
                        <select id="cohort_filter" name="cohort_filter" class="form-select">
                            <option value="">Alle schooljaren</option>
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
                    <div class="col-md-12 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter toepassen</button>
                        <a href="director_portal.php" class="btn btn-secondary">Filters resetten</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Aanwezigheidstabel -->
    <div class="container">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Overzicht van aanwezigheidsgegevens</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Klas</th>
                                <th>Vak</th>
                                <th>Lesuur</th>
                                <th>Tijd</th>
                                <th>Student Naam</th>
                                <th>Student Email</th>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
