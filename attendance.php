<?php
// attendance.php
session_start();
require 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name']);
$userRole = htmlspecialchars($_SESSION['user_role']);

if (!isset($_GET['assignment_id'])) {
    die("Assignment niet gespecificeerd.");
}
$assignmentID = intval($_GET['assignment_id']);

// Verify assignment belongs to teacher and get class info
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

// Set date (default today)
$attendanceDate = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

// Get all students in the class
$sql2 = "SELECT StudID, UserID, Name, email FROM student WHERE ClassID = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $classID);
$stmt2->execute();
$studentsResult = $stmt2->get_result();

// Get available lesson hours
$sql3 = "SELECT LesuurID, LesuurNummer, StartTijd, EindTijd FROM lesuren ORDER BY LesuurNummer";
$resultLesuren = $conn->query($sql3);
$lesuren = [];
while($row = $resultLesuren->fetch_assoc()){
    $lesuren[] = $row;
}

// Get available cohorts
$sqlCohorts = "SELECT CohortID, SchoolYear FROM cohorts";
$resultCohorts = $conn->query($sqlCohorts);
$cohorts = [];
while($row = $resultCohorts->fetch_assoc()){
    $cohorts[] = $row;
}

// Get available periods
$sqlPeriods = "SELECT PeriodID, PeriodName FROM perioden";
$resultPeriods = $conn->query($sqlPeriods);
$periods = [];
while($row = $resultPeriods->fetch_assoc()){
    $periods[] = $row;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentie voor <?= htmlspecialchars($className) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* [Keep all the CSS styles from previous answer] */
        :root {
            --primary: #2e5f89;
            --secondary: #3ba3ff;
            --accent: #ff4757;
            --background: linear-gradient(135deg, #1a2f4b, #2a5298);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--background); color: white; display: flex; min-height: 100vh; }

        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            padding: 25px;
            display: flex;
            flex-direction: column;
            box-shadow: 5px 0 30px rgba(0, 0, 0, 0.2);
        }

        .sidebar h2 {
            font-size: 1.8rem;
            margin-bottom: 40px;
            color: var(--secondary);
            text-align: center;
            font-weight: 700;
        }

        .sidebar a {
            text-decoration: none;
            color: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-radius: 12px;
            margin: 8px 0;
            transition: all 0.3s ease;
        }

        .sidebar a:hover, .active {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(10px);
        }

        .sidebar i {
            margin-right: 15px;
            font-size: 1.2rem;
            color: var(--secondary);
        }

        .main-content {
            flex: 1;
            padding: 40px;
            position: relative;
        }

        .attendance-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-top: 40px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            color: white;
        }

        .attendance-table th {
            background: rgba(255, 255, 255, 0.15);
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--secondary);
        }

        .attendance-table td {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .attendance-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            color: white;
            margin: 10px 0;
            width: 100%;
        }

        .form-control:focus {
            outline: 2px solid var(--secondary);
        }

        .submit-btn {
            background: var(--secondary);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 163, 255, 0.3);
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            border-radius: 6px;
            transition: background 0.3s ease;
        }

        .checkbox-label:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                padding: 15px;
            }
            .sidebar h2 { font-size: 0; }
            .sidebar a span { display: none; }
            .sidebar i { margin-right: 0; }
            .attendance-container { padding: 15px; }
            .attendance-table th, .attendance-table td { padding: 10px; }
        }

        .form-control {
    background: rgba(255, 255, 255, 0.15);
    border: 2px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 12px 20px;
    color: white;
    margin: 10px 0;
    width: 100%;
    transition: all 0.3s ease;
    font-weight: 500;
}

.form-control:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: var(--secondary);
}

.form-control:focus {
    outline: 2px solid var(--secondary);
    box-shadow: 0 0 15px rgba(59, 163, 255, 0.3);
}

/* Style dropdown options */
.form-control option {
    background: rgba(30, 30, 30, 0.95);
    color: white;
    padding: 10px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Style the dropdown arrow */
.form-control::-ms-expand { display: none; }
.form-control {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23ffffff'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 1em;
}

.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 15px 30px;
    border-radius: 10px;
    margin: 20px 0;
    border: 2px solid #c3e6cb;
    text-align: center;
    animation: fadeIn 0.5s;
    position: relative;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.success-message i {
    margin-right: 10px;
    color: #28a745;
}
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>NATIN-MBO</h2>
        <a href="dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a class="active"><i class="fas fa-list"></i><span>Presentielijst</span></a>
        <p style="text-align: center; margin-top: auto;"><a href="logout.php">Logout</a></p>
        <div class="profile">
            <img src="https://via.placeholder.com/50" alt="Profielfoto">
            <div>
                <p><?= $userName ?></p>
                <small><?= $userRole ?></small>
            </div>
        </div>
    </div>

    <div class="main-content">
    <?php if (isset($_GET['success']) && $_GET['success'] == 1) : ?>
    <div class="success-message">
        <i class="fas fa-check-circle"></i>
        Presentie is opgeslagen!
    </div>
<?php endif; ?>
        <div class="attendance-container">
            <h1 style="margin-bottom: 20px; color: var(--secondary);">
                <?= htmlspecialchars($subjectName) ?> - <?= htmlspecialchars($className) ?>
            </h1>
            
            <form method="post" action="process_attendance.php">
                <input type="hidden" name="assignment_id" value="<?= $assignmentID ?>">
                <input type="hidden" name="attendance_date" value="<?= $attendanceDate ?>">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <div>
                        <label>Cohort:</label>
                        <select name="cohort" class="form-control" required>
                            <?php foreach($cohorts as $cohort): ?>
                                <option value="<?= $cohort['CohortID'] ?>">
                                    <?= htmlspecialchars($cohort['SchoolYear']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Periode:</label>
                        <select name="period" class="form-control" required>
                            <?php foreach($periods as $period): ?>
                                <option value="<?= $period['PeriodID'] ?>">
                                    <?= htmlspecialchars($period['PeriodName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Student Naam</th>
                            <th>Email</th>
                            <th>Lesuren</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $studentsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['Name']) ?></td>
                            <td><?= htmlspecialchars($student['email']) ?></td>
                            <td>
                                <div class="checkbox-group">
                                    <?php foreach($lesuren as $index => $lesuur): ?>
                                        <label class="checkbox-label">
                                            <input type="checkbox" 
                                                   name="lesuur[<?= $student['UserID'] ?>][]" 
                                                   value="<?= $lesuur['LesuurID'] ?>"
                                                   style="accent-color: var(--secondary);">
                                            Lesuur <?= $lesuur['LesuurNummer'] ?> 
                                            (<?= date("H:i", strtotime($lesuur['StartTijd'])) ?> - <?= date("H:i", strtotime($lesuur['EindTijd'])) ?>)
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                            <td>
                                <select name="attendance[<?= $student['UserID'] ?>]" class="form-control">
                                    <option value="Aanwezig">Aanwezig</option>
                                    <option value="Afwezig">Afwezig</option>
                                    <option value="Ziek">Ziek</option>
                                    <option value="Laat">Laat</option>
                                    <option value="Vrijstelling">Vrijstelling</option>
                                </select>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div style="text-align: right; margin-top: 30px;">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Presentie Opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php
$stmt->close();
$stmt2->close();
$conn->close();
?>