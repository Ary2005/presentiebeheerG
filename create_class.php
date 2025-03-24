<?php
session_start();

// Database Configuration
$dbConfig = [
    'dsn' => "mysql:host=127.0.0.1;dbname=role_management_db3;charset=utf8mb4",
    'username' => "root",
    'password' => "",
    'options' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
];

try {
    $pdo = new PDO($dbConfig['dsn'], $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
} catch (PDOException $e) {  
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO classes (ClassName, Cohort) VALUES (?, ?)");
        $stmt->execute([
            htmlspecialchars(trim($_POST['classname'])),
            htmlspecialchars(trim($_POST['cohort']))
        ]);
        $_SESSION['message'] = "Klas succesvol aangemaakt!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Fout bij het aanmaken van de klas: " . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all classes
$classes = $pdo->query("SELECT * FROM classes ORDER BY ClassName")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheer Klassen</title>
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
                        <h3 class="mb-0">Klas Beheer</h3>
                    </div>
                    
                    <div class="card-body">
                        <!-- Navigation -->
                        <div class="mb-4 d-flex gap-2">
                            <a href="home.html" class="btn btn-outline-primary">Login Scherm</a>
                            <a href="beheerStud.php" class="btn btn-outline-primary">Studenten</a>
                            <a href="beheerDocent.php" class="btn btn-outline-primary">Docenten</a>
                            <a href="create_class.php" class="btn btn-primary">Nieuwe Klas</a>
                            <button type="button" onclick="history.back()" class="btn btn-secondary">Terug</button>
                        </div>

                        <!-- Creation Form -->
                        <form method="POST" class="form-section p-4 mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="classname" class="form-label">Klas Naam</label>
                                    <input type="text" name="classname" id="classname" 
                                           class="form-control" placeholder="Bijv. Class 1A" required>
                                </div>
                                <div class="col-md-6">
    <label for="cohort" class="form-label">Cohort</label>
    <select name="cohort" id="cohort" class="form-control" required>
        <option value="2024-2025">2024-2025</option>
        <option value="2025-2026">2025-2026</option>
        <option value="2026-2027">2026-2027</option>
        <option value="2027-2028">2027-2028</option>
        <option value="2028-2029">2028-2029</option>
        <option value="2029-2030">2029-2030</option>
        <option value="2030-2031">2030-2031</option>
        <option value="2031-2032">2031-2032</option>
        <option value="2032-2033">2032-2033</option>
        <option value="2033-2034">2033-2034</option>
        <option value="2034-2035">2034-2035</option>
        <option value="2035-2036">2035-2036</option>
    </select>
</div>
                            </div>

                            <div class="action-buttons mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-success">Aanmaken</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form>

                        <!-- Classes Table -->
                        <h4 class="mb-3">Bestaande Klassen</h4>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ClassID</th>
                                        <th>Klas Naam</th>
                                        <th>Cohort</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($class['ClassID']) ?></td>
                                            <td><?= htmlspecialchars($class['ClassName']) ?></td>
                                            <td><?= htmlspecialchars($class['Cohort']) ?></td>
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
</body>
</html>