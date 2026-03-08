<?php
require_once 'includes/functions.php';
requireLogin();

require_once 'includes/db_connect.php';

// Get user stats
$stmt = $pdo->prepare("SELECT COUNT(*) as photo_count FROM photos WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$photo_count = $stmt->fetch()['photo_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as schedule_count FROM schedules WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$_SESSION['user_id']]);
$schedule_count = $stmt->fetch()['schedule_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - POSO</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/logo.png">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="header-logo">
                    <img src="assets/images/logo.png" alt="POSO Logo">
                    <h2>POSO</h2>
                </div>
                <button class="mobile-menu-btn" onclick="toggleMenu()">☰</button>
                <nav class="header-nav" id="headerNav">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="take_picture.php">Take Picture</a>
                    <a href="view_images.php">View Images</a>
                    <a href="schedule.php">Schedule</a>
                    <div class="user-info">
                        <span>Hi, <?php echo $_SESSION['firstname']; ?>!</span>
                        <a href="logout.php" class="btn btn-outline">Logout</a>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1>Welcome, <?php echo $_SESSION['user_name']; ?>!</h1>
                <p>This is your POSO dashboard. You can take pictures, view your images, and manage your schedules here.</p>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-grid">
                <div class="dashboard-card" onclick="window.location.href='view_images.php'">
                    <div class="icon">📷</div>
                    <h3>View Images Taken</h3>
                    <p>You have <?php echo $photo_count; ?> photo(s) in your gallery. Click to view all your captured images.</p>
                </div>

                <div class="dashboard-card" onclick="window.location.href='schedule.php'">
                    <div class="icon">📅</div>
                    <h3>Schedule</h3>
                    <p>You have <?php echo $schedule_count; ?> pending schedule(s). Manage your appointments and events.</p>
                </div>

                <div class="dashboard-card" onclick="window.location.href='take_picture.php'">
                    <div class="icon">📸</div>
                    <h3>Take Picture</h3>
                    <p>Capture photos using your device camera. Images are saved with timestamps automatically.</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="admin-stats">
                <div class="stat-card">
                    <h3><?php echo $photo_count; ?></h3>
                    <p>Total Photos</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $schedule_count; ?></h3>
                    <p>Pending Schedules</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo date('M d'); ?></h3>
                    <p>Today</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> POSO - Public Order and Safety Office | City of San Carlos, Pangasinan</p>
        </footer>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById('headerNav').classList.toggle('active');
        }
    </script>
</body>
</html>
