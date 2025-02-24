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

// Helper Function
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update'])) {
            $stmt = $pdo->prepare("UPDATE student SET Name=?, email=?, Richting=?, Cohort=?, ClassID=? WHERE StudID=?");
$stmt->execute([
    sanitizeInput($_POST['name']),
    sanitizeInput($_POST['email']),
    sanitizeInput($_POST['richting']),
    sanitizeInput($_POST['cohort']),
    sanitizeInput($_POST['klas']),
    sanitizeInput($_POST['studid'])
]);
            $_SESSION['message'] = "Student succesvol bijgewerkt!";
        } elseif (isset($_POST['delete'])) {
            $stmt = $pdo->prepare("DELETE FROM student WHERE StudID=?");
            $stmt->execute([sanitizeInput($_POST['studid'])]);
            $_SESSION['message'] = "Student succesvol verwijderd!";
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Operatie mislukt: " . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

$classes = $pdo->query("SELECT ClassID, ClassName FROM classes ORDER BY ClassName")->fetchAll(PDO::FETCH_ASSOC);
$students = $pdo->query("
    SELECT student.*, classes.ClassName
    FROM student
    INNER JOIN classes ON student.ClassID = classes.ClassID
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheer Studenten</title>
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
                        <h3 class="mb-0">Studenten Beheer</h3>
                    </div>
                    
                    <div class="card-body">
                        <!-- Navigation -->
                        <div class="mb-4 d-flex gap-2">
                            <a href="home.html" class="btn btn-outline-primary">Login Scherm</a>
                            <a href="beheerStud.php" class="btn btn-outline-primary">Studenten</a>
                            <a href="beheerDocent.php" class="btn btn-outline-primary">Docenten</a>
                            <a href="create_class.php" class="btn btn-outline-primary">Nieuwe Klas</a>
                            <button type="button" onclick="history.back()" class="btn btn-secondary">Terug</button>
                        </div>

                        <!-- Student Form -->
                        <form method="POST" class="form-section p-4 mb-4">
                            <input type="hidden" name="studid" id="studid">
                            
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <input type="text" name="name" class="form-control" placeholder="Naam" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="col-md-2">
                                    <select name="richting" class="form-select">
                                        <option value="NONE">(Geen richting)</option>
                                        <option value="ICT">ICT</option>
                                        <option value="AV">AV</option>
                                        <option value="INFR">INFR</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="text" name="cohort" class="form-control" placeholder="Cohort">
                                </div>
                                <div class="col-md-2">
                                    <select name="klas" class="form-select" required>
                                        <option value="">Selecteer Klas</option>
                                        <?php foreach($classes as $class): ?>
                                            <option value="<?= $class['ClassID'] ?>">
                                                <?= htmlspecialchars($class['ClassName']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="action-buttons mt-4 d-flex gap-2">
                                <button type="submit" name="update" class="btn btn-warning">Bijwerken</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form>

                        <!-- Students Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Naam</th>
                                        <th>Email</th>
                                        <th>Richting</th>
                                        <th>Cohort</th>
                                        <th>Klas</th>
                                        <th>Acties</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($student['StudID']) ?></td>
                                            <td><?= htmlspecialchars($student['Name']) ?></td>
                                            <td><?= htmlspecialchars($student['email']) ?></td>
                                            <td><?= htmlspecialchars($student['Richting'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($student['Cohort'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($student['ClassName'] ?? '-') ?></td>
                                            <td>
                                                <button onclick="editStudent(<?= htmlspecialchars(json_encode($student), ENT_QUOTES) ?>)"
                                                    class="btn btn-sm btn-outline-primary me-1">
                                                    ✏️
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="studid" value="<?= $student['StudID'] ?>">
                                                    <button type="submit" name="delete" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Weet u het zeker?')">
                                                        ❌
                                                    </button>
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
        function editStudent(student) {
            document.getElementById('studid').value = student.StudID;
            document.querySelector('[name="name"]').value = student.Name;
            document.querySelector('[name="email"]').value = student.email;
            document.querySelector('[name="richting"]').value = student.Richting || 'NONE';
            document.querySelector('[name="cohort"]').value = student.Cohort || '';
            document.querySelector('[name="klas"]').value = student.ClassID || '';
            
            // Scroll to form
            document.querySelector('form').scrollIntoView({ behavior: 'smooth' });
        }

        document.querySelector('form').addEventListener('reset', () => {
            document.getElementById('studid').value = '';
        });
    </script>
</body>
</html>