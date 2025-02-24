<?php
session_start();

// Database Configuration
$dbConfig = [
    'dsn' => "mysql:host=127.0.0.1;dbname=role_management_db;charset=utf8mb4",
    'username' => "root",
    'password' => "",
    'options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
];

try {
    $pdo = new PDO($dbConfig['dsn'], $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
} catch (PDOException $e) {  
    die("Database connection failed: " . $e->getMessage());
}

// Fetch classes and subjects
$classes = $pdo->query("SELECT ClassID, ClassName FROM classes ORDER BY ClassName")->fetchAll(PDO::FETCH_ASSOC);
$subjects = $pdo->query("SELECT SubjectID, SubjectName FROM subjects ORDER BY SubjectName")->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE teacher SET Name = ?, email = ?, Richting = ?, ClassID = ?, Cohort = ? WHERE TeacherID = ?");
            $stmt->execute([
                htmlspecialchars(trim($_POST['name'])),
                htmlspecialchars(trim($_POST['email'])),
                htmlspecialchars(trim($_POST['richting'])),
                htmlspecialchars(trim($_POST['class_id'])),
                htmlspecialchars(trim($_POST['cohort'])),
                htmlspecialchars(trim($_POST['id']))
            ]);
            $_SESSION['message'] = "Docent succesvol bijgewerkt!";
        } 
        elseif (isset($_POST['delete'])) {
            $stmt = $pdo->prepare("DELETE FROM teacher WHERE TeacherID = ?");
            $stmt->execute([htmlspecialchars(trim($_POST['id']))]);
            $_SESSION['message'] = "Docent succesvol verwijderd!";
        } 
        elseif (isset($_POST['assign_subject'])) {
            $stmt = $pdo->prepare("INSERT INTO teacher_assignments (TeacherID, SubjectID, ClassID) VALUES (?, ?, ?)");
            $stmt->execute([
                htmlspecialchars(trim($_POST['assign_teacher_id'])),
                htmlspecialchars(trim($_POST['subject_id'])),
                htmlspecialchars(trim($_POST['assign_class_id']))
            ]);
            $_SESSION['message'] = "Vak succesvol toegekend!";
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Fout: " . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch teachers with their details
$query = "
    SELECT teacher.*, classes.ClassName, 
           GROUP_CONCAT(subjects.SubjectName SEPARATOR ', ') AS Subjects
    FROM teacher 
    LEFT JOIN classes ON teacher.ClassID = classes.ClassID
    LEFT JOIN teacher_assignments ta ON teacher.UserID = ta.TeacherID
    LEFT JOIN subjects ON ta.SubjectID = subjects.SubjectID
    GROUP BY teacher.TeacherID
    ORDER BY teacher.Name
";
$teachers = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheer Docenten</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-shadow { box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); }
        .form-section { background: #f8f9fa; border-radius: 0.5rem; }
        .action-buttons .btn { min-width: 100px; }
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,0.05); }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Notification Alerts -->
                <?php if(isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Main Card -->
                <div class="card card-shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Docenten Beheer</h3>
                    </div>
                    
                    <div class="card-body">
                        <!-- Navigation -->
                        <div class="mb-4 d-flex gap-2">
                            <a href="home.html" class="btn btn-outline-primary">Login Scherm</a>
                            <a href="beheerStud.php" class="btn btn-outline-primary">Studenten</a>
                            <a href="beheerDocent.php" class="btn btn-outline-primary">Docenten</a>
                            <a href="create_class.php" class="btn btn-outline-primary">Klassen</a>
                            <button type="button" onclick="history.back()" class="btn btn-secondary">Terug</button>
                        </div>

                        <!-- Edit Form -->
                        <form method="POST" class="form-section p-4 mb-4">
                            <input type="hidden" name="id" id="teacher_id">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Naam</label>
                                    <input type="text" name="name" id="name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="richting" class="form-label">Richting</label>
                                    <select name="richting" id="richting" class="form-control" required>
                                        <option value="NONE">(NONE)</option>
                                        <option value="ICT">ICT</option>
                                        <option value="AV">AV</option>
                                        <option value="INFR">INFR</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="klas" class="form-label">Klas</label>
                                    <select name="class_id" id="klas" class="form-control" required>
                                        <option value="">Selecteer Klas</option>
                                        <?php foreach($classes as $class): ?>
                                            <option value="<?= htmlspecialchars($class['ClassID']) ?>">
                                                <?= htmlspecialchars($class['ClassName']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="cohort" class="form-label">Cohort</label>
                                    <input type="text" name="cohort" id="cohort" class="form-control" placeholder="Bijv. 2024-2025" required>
                                </div>
                            </div>

                            <div class="action-buttons mt-4 d-flex gap-2">
                                <button type="submit" name="update" class="btn btn-warning">Bewerken</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form>

                        <!-- Subject Assignment Form -->
                        <div class="form-section p-4 mt-5">
                            <h4 class="mb-4">Vak toekennen</h4>
                            <form method="POST">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Docent</label>
                                        <select name="assign_teacher_id" class="form-control" required>
                                            <option value="">Selecteer Docent</option>
                                            <?php foreach($teachers as $teacher): ?>
                                                <option value="<?= htmlspecialchars($teacher['UserID']) ?>">
                                                    <?= htmlspecialchars($teacher['Name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Vak</label>
                                        <select name="subject_id" class="form-control" required>
                                            <option value="">Selecteer Vak</option>
                                            <?php foreach($subjects as $subject): ?>
                                                <option value="<?= htmlspecialchars($subject['SubjectID']) ?>">
                                                    <?= htmlspecialchars($subject['SubjectName']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Klas</label>
                                        <select name="assign_class_id" class="form-control" required>
                                            <option value="">Selecteer Klas</option>
                                            <?php foreach($classes as $class): ?>
                                                <option value="<?= htmlspecialchars($class['ClassID']) ?>">
                                                    <?= htmlspecialchars($class['ClassName']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="action-buttons mt-4">
                                    <button type="submit" name="assign_subject" class="btn btn-success">Toekennen</button>
                                </div>
                            </form>
                        </div>

                        <!-- Teachers Table -->
                        <h4 class="mb-3">Docenten Lijst</h4>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Naam</th>
                                        <th>Email</th>
                                        <th>Richting</th>
                                        <th>Klas</th>
                                        <th>Cohort</th>
                                        <th>Vakken</th>
                                        <th>Acties</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($teacher['TeacherID']) ?></td>
                                            <td><?= htmlspecialchars($teacher['Name']) ?></td>
                                            <td><?= htmlspecialchars($teacher['email']) ?></td>
                                            <td><?= htmlspecialchars($teacher['Richting'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($teacher['ClassName'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($teacher['Cohort'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($teacher['Subjects'] ?? 'N/A') ?></td>
                                            <td>
                                                <button onclick='editTeacher(<?= json_encode($teacher) ?>)' class="btn btn-info btn-sm">Bewerken</button>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $teacher['TeacherID'] ?>">
                                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Verwijderen</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editTeacher(teacher) {
            document.getElementById('teacher_id').value = teacher.TeacherID;
            document.getElementById('name').value = teacher.Name;
            document.getElementById('email').value = teacher.email;
            document.getElementById('richting').value = teacher.Richting || 'NONE';
            document.getElementById('klas').value = teacher.ClassID || '';
            document.getElementById('cohort').value = teacher.Cohort || '';
            document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>