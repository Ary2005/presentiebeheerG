<?php
// dashboard.php
session_start();
require 'dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: home.html");
    exit();
}

// Get user data
$userName = htmlspecialchars($_SESSION['user_name']);
$userRole = htmlspecialchars($_SESSION['user_role']);
$userID = $_SESSION['user_id'];

// Get teacher assignments
$sql = "SELECT ta.AssignmentID, s.SubjectName, c.ClassName
        FROM teacher_assignments ta
        JOIN subjects s ON ta.SubjectID = s.SubjectID
        JOIN classes c ON ta.ClassID = c.ClassID
        WHERE ta.TeacherID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$assignmentsResult = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2e5f89;
            --secondary: #3ba3ff;
            --accent: #ff4757;
            --background: linear-gradient(135deg, #1a2f4b, #2a5298);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--background); color: white; display: flex; min-height: 100vh; overflow-x: hidden; }

        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            padding: 25px;
            display: flex;
            flex-direction: column;
            box-shadow: 5px 0 30px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 2;
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
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .sidebar a::before {
            content: '';
            position: absolute;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: left 0.4s ease;
        }

        .sidebar a:hover::before {
            left: 0;
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

        .search-bar {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 15px;
            padding: 15px 25px;
            width: 60%;
            border: none;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .search-bar:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 163, 255, 0.3);
            width: 65%;
        }

        .profile {
            display: flex;
            align-items: center;
            position: absolute;
            bottom: 30px;
            left: 30px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 15px;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }

        .profile:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: scale(1.05);
        }

        .profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            border: 2px solid var(--secondary);
        }

        .welcome-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 60px 0;
            position: relative;
        }

        .welcome-text h1 {
            font-size: 3.5rem;
            background: linear-gradient(to right, #fff, var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
            margin-bottom: 15px;
        }

        .interactive-image {
            width: 600px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            filter: drop-shadow(0 20px 30px rgba(0, 0, 0, 0.3));
            position: relative;
            z-index: 1;
        }

        .assignments-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-top: 40px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }

        .assignments-table {
            width: 100%;
            border-collapse: collapse;
            color: white;
        }

        .assignments-table th {
            background: rgba(255, 255, 255, 0.15);
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--secondary);
        }

        .assignments-table td {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .assignments-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .action-link {
            color: var(--secondary);
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .action-link:hover {
            background: rgba(59, 163, 255, 0.2);
            transform: translateX(5px);
        }

        

        .welcome-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(10px);
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                padding: 15px;
            }
            .sidebar h2 { font-size: 0; }
            .sidebar a span { display: none; }
            .sidebar i { margin-right: 0; }
            .interactive-image { width: 300px; }
            .welcome-text h1 { font-size: 2rem; }
            .assignments-container { padding: 15px; }
            .assignments-table th, .assignments-table td { padding: 10px; }
        }

        .highlight-flash {
        animation: highlightFade 2s ease-out;
    }

    @keyframes highlightFade {
        0% { background: rgba(59, 163, 255, 0.3); }
        100% { background: transparent; }
    }
    </style>
</head>
<body>
    

    <!--<div class="welcome-modal" id="welcomeModal">
        <div class="welcome-content">
            <i class="fas fa-check-circle welcome-icon"></i>
            <h2>Welkom terug! 👋</h2>
            <p>Succesvol ingelogd als <span id="userName"><?= $userName ?></span></p>
        </div>
    </div>-->

    <div class="sidebar">
        <h2>NATIN-MBO</h2>
        <a href="#" class="active"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <!--<a href="teacher_dashboard.php"><i class="fas fa-book"></i><span>Vakken en Klassen</span></a>-->
        <a ><i class="fas fa-list"></i><span>Presentielijst</span></a>
        <p style="text-align: center;"><a href="logout.php">Logout</a></p>
        <div class="profile">
            <img src="https://via.placeholder.com/50" alt="Profielfoto">
            <div>
                <p id="profileName"><?= $userName ?></p>
                <small id="profileRole"><?= $userRole ?></small>
            </div>
        </div>
    </div>

    <div class="main-content">
        <input type="text" class="search-bar" placeholder="Zoeken...">
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>Welkom<br><span id="welcomeName"><?= explode(' ', $userName)[0] ?></span>! 🚀</h1>
                <p>Docenten dashboard</p>
            </div>
            <div class="interactive-image-container">
                <img src="Rectangle 28.png" 
                     class="interactive-image attendance-animation" 
                     alt="Attendance Illustration"
                     id="attendanceImage"
                     onclick="animateImage(this)">
            </div>
        </div>

        <div class="assignments-container">
            <h2 style="margin-bottom: 25px; color: var(--secondary);">Uw Opdrachten</h2>
            <table class="assignments-table">
                <thead>
                    <tr>
                        <th>Vak</th>
                        <th>Klas</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $assignmentsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['SubjectName']) ?></td>
                        <td><?= htmlspecialchars($row['ClassName']) ?></td>
                        <td>
                            <a href="attendance.php?assignment_id=<?= $row['AssignmentID'] ?>" class="action-link">
                                <i class="fas fa-clipboard-list"></i>
                                Neem/Bekijk Aanwezigheid
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        

        // Modal animation
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('welcomeModal');
            modal.style.display = 'flex';
            
            setTimeout(() => {
                modal.style.animation = 'fadeOut 0.8s forwards';
                setTimeout(() => modal.style.display = 'none', 800);
            }, 3500);
        });

        
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>