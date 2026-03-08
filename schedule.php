<?php
require_once 'includes/functions.php';
requireLogin();

require_once 'includes/db_connect.php';

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $schedule_date = $_POST['schedule_date'];
    $schedule_time = $_POST['schedule_time'];
    $location = sanitize($_POST['location']);
    
    if (empty($title) || empty($schedule_date)) {
        $error = 'Please fill in the title and date.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO schedules (user_id, title, description, schedule_date, schedule_time, location) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $title, $description, $schedule_date, $schedule_time, $location])) {
            $success = 'Schedule added successfully!';
        } else {
            $error = 'Failed to add schedule.';
        }
    }
}

// Handle status update
if (isset($_GET['complete']) && is_numeric($_GET['complete'])) {
    $stmt = $pdo->prepare("UPDATE schedules SET status = 'completed' WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['complete'], $_SESSION['user_id']]);
    header('Location: schedule.php');
    exit();
}

if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $stmt = $pdo->prepare("UPDATE schedules SET status = 'cancelled' WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['cancel'], $_SESSION['user_id']]);
    header('Location: schedule.php');
    exit();
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM schedules WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['delete'], $_SESSION['user_id']]);
    header('Location: schedule.php');
    exit();
}

// Get all user schedules
$stmt = $pdo->prepare("SELECT * FROM schedules WHERE user_id = ? ORDER BY schedule_date DESC, schedule_time DESC");
$stmt->execute([$_SESSION['user_id']]);
$schedules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - POSO</title>
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

        <!-- Content -->
        <div class="dashboard-content">
            <div class="schedule-section">
                <h2 style="color: var(--primary-green); margin-bottom: 5px;">📅 Schedule</h2>
                <p style="color: #666; margin-bottom: 25px;">Manage your appointments and events</p>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <!-- Add Schedule Form -->
                <div class="schedule-form">
                    <h3 style="color: var(--primary-green); margin-bottom: 15px;">Add New Schedule</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" placeholder="Enter schedule title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3" placeholder="Add details..."></textarea>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="schedule_date">Date *</label>
                                <input type="date" id="schedule_date" name="schedule_date" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="schedule_time">Time</label>
                                <input type="time" id="schedule_time" name="schedule_time">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" placeholder="Enter location">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Add Schedule</button>
                    </form>
                </div>
                
                <!-- Schedule List -->
                <div class="schedule-list">
                    <h3 style="color: var(--primary-green); margin-bottom: 15px;">Your Schedules</h3>
                    
                    <?php if (empty($schedules)): ?>
                        <div class="empty-state">
                            <div class="icon">📅</div>
                            <h3>No Schedules Yet</h3>
                            <p>Add your first schedule using the form above.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($schedules as $schedule): ?>
                            <div class="schedule-item <?php echo $schedule['status']; ?>">
                                <div class="schedule-info">
                                    <h4><?php echo htmlspecialchars($schedule['title']); ?></h4>
                                    <p>
                                        📅 <?php echo date('F d, Y', strtotime($schedule['schedule_date'])); ?>
                                        <?php if ($schedule['schedule_time']): ?>
                                            ⏰ <?php echo date('h:i A', strtotime($schedule['schedule_time'])); ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($schedule['location']): ?>
                                        <p>📍 <?php echo htmlspecialchars($schedule['location']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($schedule['description']): ?>
                                        <p style="margin-top: 5px;"><?php echo htmlspecialchars($schedule['description']); ?></p>
                                    <?php endif; ?>
                                    <span class="badge badge-<?php echo $schedule['status']; ?>" style="margin-top: 10px;">
                                        <?php echo ucfirst($schedule['status']); ?>
                                    </span>
                                </div>
                                <div class="schedule-actions">
                                    <?php if ($schedule['status'] === 'pending'): ?>
                                        <a href="?complete=<?php echo $schedule['id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 0.8rem;">Complete</a>
                                        <a href="?cancel=<?php echo $schedule['id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.8rem;">Cancel</a>
                                    <?php endif; ?>
                                    <a href="?delete=<?php echo $schedule['id']; ?>" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.8rem;" onclick="return confirm('Delete this schedule?')">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
        
        // Set minimum date to today
        document.getElementById('schedule_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
