<?php
// dashboard.php
session_start();
require 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: home.html");
    exit();
}

// Get user data from session
$userName = htmlspecialchars($_SESSION['user_name']);
$userRole = htmlspecialchars($_SESSION['user_role']);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2e5f89;
            --secondary: #3ba3ff;
            --accent: #ff4757;
            --background: linear-gradient(135deg, #1a2f4b, #2a5298);
        }

        /* Rest of your CSS styles remain unchanged */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: var(--background); color: white; display: flex; min-height: 100vh; overflow-x: hidden; }
        /* ... (keep all other CSS rules exactly as in your second code example) ... */

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

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transition: left 0.6s;
        }

        .card:hover::after {
            left: 100%;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
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

        .interactive-image-container {
            position: relative;
        }

        .interactive-image:hover {
            transform: scale(1.1) rotate(2deg);
            filter: drop-shadow(0 30px 40px rgba(0, 0, 0, 0.4));
        }

        .attendance-animation {
            animation: float 5s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(3deg); }
        }

        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: particle-float linear infinite;
        }

        @keyframes particle-float {
            to { transform: translateY(-100vh) rotate(360deg); }
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

        .welcome-content {
            background: linear-gradient(135deg, rgba(42, 82, 152, 0.9), rgba(30, 60, 114, 0.9));
            padding: 50px 80px;
            border-radius: 30px;
            text-align: center;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.4);
            position: relative;
            transform: scale(0);
            animation: scaleUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            border: 2px solid rgba(255, 255, 255, 0.1);
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
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>

    <!-- Welcome Modal -->
    <div class="welcome-modal" id="welcomeModal">
        <div class="welcome-content">
            <i class="fas fa-check-circle welcome-icon"></i>
            <h2>Welkom terug! 👋</h2>
            <p>Succesvol ingelogd als <span id="userName"><?= $userName ?></span></p>
        </div>
    </div>

    <div class="sidebar">
        <h2>NATIN-MBO</h2>
        <a href="#" class="active"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="teacher_dashboard.php"><i class="fas fa-book"></i><span>Mijn vakken</span></a>
        <a href="#"><i class="fas fa-users"></i><span>Mijn klassen</span></a>
        <a href="presentieLijst.php"><i class="fas fa-list"></i><span>Presentielijst</span></a>
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
        <div class="card-container">
            <div class="card">
                <i class="fas fa-chart-line fa-3x" style="color: #4CAF50;"></i>
                <h3>Afwezigen Vorige Week</h3>
                <canvas id="absenceChart"></canvas>
            </div>
            <div class="card">
                <i class="fas fa-exclamation-triangle fa-3x" style="color: #ff4757;"></i>
                <h3>Gevaarzone (80%)</h3>
                <p class="stat-number">4</p>
            </div>
            <div class="card">
                <i class="fas fa-calendar-alt fa-3x" style="color: #3ba3ff;"></i>
                <h3>Afwezigen Deze Maand</h3>
                <p class="stat-number">16</p>
            </div>
        </div>
    </div>

    <script>
        // Particle animation and other JS remains unchanged
        function createParticles() {
            const particles = document.getElementById('particles');
            for (let i = 0; i < 50; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.width = particle.style.height = Math.random() * 3 + 2 + 'px';
                particle.style.animationDuration = Math.random() * 3 + 2 + 's';
                particles.appendChild(particle);
            }
        }
        createParticles();

        // Enhanced chart
        new Chart(document.getElementById('absenceChart'), {
            type: 'doughnut',
            data: {
                labels: ['Ziek', 'Te laat', 'Afwezig', 'Vakantie'],
                datasets: [{
                    data: [5, 3, 2, 4],
                    backgroundColor: ['#4CAF50', '#FFC107', '#FF4757', '#3ba3ff'],
                    borderWidth: 0,
                }]
            },
            options: {
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { bodyFont: { family: 'Poppins' } }
                }
            }
        });

        // Modal animation
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('welcomeModal');
            modal.style.display = 'flex';
            
            setTimeout(() => {
                modal.style.animation = 'fadeOut 0.8s forwards';
                setTimeout(() => modal.style.display = 'none', 800);
            }, 3500);
        });

        // Dynamic background particles
        document.addEventListener('mousemove', (e) => {
            const particles = document.querySelectorAll('.particle');
            particles.forEach(particle => {
                const speed = parseFloat(particle.style.animationDuration);
                particle.style.transform = `translate(${e.clientX * 0.02}px, ${e.clientY * 0.02}px)`;
            });
        });
    </script>
</body>
</html>