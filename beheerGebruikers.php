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

// Constants and Mappings
$roleMapping = [
    'Admin'    => 1,
    'Teacher'  => 2,
    'Student'  => 3,
    'Director' => 4,
    'RC'       => 5
];
$inverseRoleMapping = array_flip($roleMapping);

// Helper Functions
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function getClasses($pdo) {
    return $pdo->query("SELECT ClassID, ClassName FROM classes ORDER BY ClassName")->fetchAll(PDO::FETCH_ASSOC);
}

// Form Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        $roleText = $_POST['role'];
        $roleID = $roleMapping[$roleText] ?? 3;
        
        // Handle Director special case
        $richting = $roleText === 'Director' ? null : sanitizeInput($_POST['richting']);
        $cohort = $roleText === 'Director' ? null : sanitizeInput($_POST['cohort']);
        $klas = $roleText === 'Director' ? null : sanitizeInput($_POST['klas']);

        if (isset($_POST['create'])) {
            // Create User
            $stmt = $pdo->prepare("INSERT INTO users (Name, Password, Email, RoleID, Richting, Cohort, Klas) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $name,
                password_hash($password, PASSWORD_DEFAULT),
                $email,
                $roleID,
                $richting,
                $cohort,
                $klas
            ]);
            
            $userId = $pdo->lastInsertId();
            $_SESSION['message'] = "User created successfully!";

            // Handle role-specific tables
            if ($roleText === 'Teacher') {
                $pdo->prepare("INSERT INTO teacher (UserID, Name, email, Richting, ClassID, Cohort) 
                              VALUES (?, ?, ?, ?, ?, ?)")
                    ->execute([$userId, $name, $email, $richting, $klas, $cohort]);
            } elseif ($roleText === 'Student') {
                $pdo->prepare("INSERT INTO student (UserID, Name, email, Richting, Cohort, ClassID) 
                              VALUES (?, ?, ?, ?, ?, ?)")
                    ->execute([$userId, $name, $email, $richting, $cohort, $klas]);
            }
            
        } elseif (isset($_POST['update'])) {
            // Update User
            $userId = $_POST['id'];
            $stmt = $pdo->prepare("UPDATE users SET 
                Name=?, Email=?, RoleID=?, Richting=?, Cohort=?, Klas=?
                WHERE ID=?");
            $stmt->execute([$name, $email, $roleID, $richting, $cohort, $klas, $userId]);
            $_SESSION['message'] = "User updated successfully!";
            
        } elseif (isset($_POST['delete'])) {
            // Delete User
            $userId = $_POST['id'];
            $pdo->prepare("DELETE FROM teacher WHERE UserID = ?")->execute([$userId]);
            $pdo->prepare("DELETE FROM student WHERE UserID = ?")->execute([$userId]);
            $pdo->prepare("DELETE FROM users WHERE ID = ?")->execute([$userId]);
            $_SESSION['message'] = "User deleted successfully!";
        }
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Operation failed: " . $e->getMessage();
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Data Fetching
$classes = getClasses($pdo);
$users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beheer Gebruikers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-shadow { box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); }
        .form-section { background: #f8f9fa; border-radius: 0.5rem; }
        .role-badge { font-size: 0.8rem; padding: 0.35rem 0.65rem; }
        .action-buttons .btn { min-width: 100px; }
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
                        <h3 class="mb-0">Gebruikers Beheer</h3>
                    </div>
                    
                    <div class="card-body">
                        <!-- Navigation -->
                        <div class="mb-4 d-flex gap-2">
                            <a href="home.html" class="btn btn-outline-primary">Login Scherm</a>
                            <a href="beheerGebruikers.php" class="btn btn-outline-primary">Gebruikers</a>
                            <a href="beheerStud.php" class="btn btn-outline-primary">Studenten</a>
                            <a href="beheerDocent.php" class="btn btn-outline-primary">Docenten</a>
                            <a href="create_class.php" class="btn btn-outline-primary">Nieuwe Klas</a>
                            <button type="button" onclick="history.back()" class="btn btn-secondary">Terug</button>
                        </div>

                        <!-- User Form -->
                        <form method="POST" class="form-section p-4 mb-4">
                            <input type="hidden" name="id" id="user_id">
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <input type="text" name="name" class="form-control" placeholder="Naam" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="password" name="password" class="form-control" 
                                           placeholder="Wachtwoord" id="passwordField" required>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <select name="role" id="role" class="form-select" required onchange="roleChanged()">
                                        <?php foreach($roleMapping as $role => $id): ?>
                                            <option value="<?= $role ?>"><?= $role ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3" id="richtingContainer">
                                    <select name="richting" class="form-select">
                                        <option value="NONE">(Geen richting)</option>
                                        <option value="ICT">ICT</option>
                                        <option value="AV">AV</option>
                                        <option value="INFR">INFR</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-3" id="cohortContainer">
    <select name="cohort" class="form-control">
        <option value="">Selecteer Cohort</option>
        <?php
        for ($startYear = 2024; $startYear <= 2035; $startYear++) {
            $endYear = $startYear + 1;
            $cohortValue = $startYear . "-" . $endYear;
            echo '<option value="' . $cohortValue . '">' . $cohortValue . '</option>';
        }
        ?>
    </select>
</div>
                                
                                <div class="col-md-3" id="klasContainer">
                                    <select name="klas" class="form-select">
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
                                <button type="submit" name="create" class="btn btn-success">Aanmaken</button>
                                <button type="submit" name="update" class="btn btn-warning d-none">Bijwerken</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form>

                        <!-- Users Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Naam</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Richting</th>
                                        <th>Cohort</th>
                                        <th>Klas</th>
                                        <th>Acties</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= $user['ID'] ?></td>
                                            <td><?= htmlspecialchars($user['Name']) ?></td>
                                            <td><?= htmlspecialchars($user['Email']) ?></td>
                                            <td>
                                                <span class="role-badge badge bg-primary">
                                                    <?= $inverseRoleMapping[$user['RoleID']] ?? 'Onbekend' ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($user['Richting'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($user['Cohort'] ?? '-') ?></td>
                                            <td>
                                                <?php if($user['Klas']): ?>
                                                    <?= array_column($classes, 'ClassName', 'ClassID')[$user['Klas']] ?? '-' ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button onclick="editUser(<?= htmlspecialchars(json_encode($user), ENT_QUOTES) ?>)"
                                                    class="btn btn-sm btn-outline-primary me-1">
                                                    ✏️
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $user['ID'] ?>">
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
        const roleMapping = <?= json_encode($inverseRoleMapping) ?>;

        function roleChanged() {
            const role = document.getElementById('role').value;
            const isDirector = role === 'Director';
            
            ['richting', 'cohort', 'klas'].forEach(field => {
                const container = document.getElementById(`${field}Container`);
                const input = document.getElementById(field);
                
                container.style.display = isDirector ? 'none' : 'block';
                input.required = !isDirector;
            });
            
            if(isDirector) {
                document.querySelectorAll('#richting, #cohort, #klas').forEach(field => field.value = '');
            }
        }

        function editUser(user) {
            document.getElementById('user_id').value = user.ID;
            document.getElementById('name').value = user.Name;
            document.getElementById('email').value = user.Email;
            document.getElementById('passwordField').removeAttribute('required');
            
            const roleName = roleMapping[user.RoleID] || 'Student';
            document.getElementById('role').value = roleName;
            roleChanged();

            document.querySelector('[name="richting"]').value = user.Richting || 'NONE';
            document.querySelector('[name="cohort"]').value = user.Cohort || '';
            document.querySelector('[name="klas"]').value = user.Klas || '';

            document.querySelector('[name="create"]').classList.add('d-none');
            document.querySelector('[name="update"]').classList.remove('d-none');
        }

        window.onload = () => {
            roleChanged();
            document.querySelector('form').addEventListener('reset', () => {
                document.querySelector('[name="create"]').classList.remove('d-none');
                document.querySelector('[name="update"]').classList.add('d-none');
                document.getElementById('passwordField').required = true;
            });
        };
    </script>
</body>
</html>