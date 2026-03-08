<?php
require_once 'includes/functions.php';
requireLogin();

require_once 'includes/db_connect.php';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $photo_id = $_GET['delete'];
    
    // Get photo info
    $stmt = $pdo->prepare("SELECT * FROM photos WHERE id = ? AND user_id = ?");
    $stmt->execute([$photo_id, $_SESSION['user_id']]);
    $photo = $stmt->fetch();
    
    if ($photo) {
        // Delete file
        if (file_exists($photo['filepath'])) {
            unlink($photo['filepath']);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?");
        $stmt->execute([$photo_id, $_SESSION['user_id']]);
        
        $success = 'Photo deleted successfully!';
    }
}

// Get all user photos
$stmt = $pdo->prepare("SELECT * FROM photos WHERE user_id = ? ORDER BY timestamp DESC");
$stmt->execute([$_SESSION['user_id']]);
$photos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Images - POSO</title>
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
            <div class="gallery-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <h2 style="color: var(--primary-green);">📷 Your Images</h2>
                        <p style="color: #666;">View and manage your captured photos</p>
                    </div>
                    <a href="take_picture.php" class="btn btn-primary">+ Take New Photo</a>
                </div>
                
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if (empty($photos)): ?>
                    <div class="empty-state">
                        <div class="icon">📷</div>
                        <h3>No Photos Yet</h3>
                        <p>You haven't taken any photos. Click "Take New Photo" to get started!</p>
                    </div>
                <?php else: ?>
                    <div class="gallery-grid">
                        <?php foreach ($photos as $photo): ?>
                            <div class="gallery-item">
                                <img src="<?php echo $photo['filepath']; ?>" alt="Photo" loading="lazy" onclick="viewImage('<?php echo $photo['filepath']; ?>', '<?php echo formatDate($photo['timestamp']); ?>', '<?php echo addslashes($photo['notes']); ?>')">
                                <div class="overlay">
                                    <small><?php echo formatDate($photo['timestamp']); ?></small>
                                    <br>
                                    <a href="?delete=<?php echo $photo['id']; ?>" class="btn btn-danger" style="padding: 4px 10px; font-size: 0.75rem; margin-top: 5px;" onclick="return confirm('Delete this photo?')">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> POSO - Public Order and Safety Office | City of San Carlos, Pangasinan</p>
        </footer>
    </div>

    <!-- Image Modal -->
    <div class="modal" id="imageModal">
        <div class="modal-content" style="max-width: 90vw;">
            <div class="modal-header">
                <h3>Photo Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <img id="modalImage" src="" alt="Full size" style="width: 100%; border-radius: 10px;">
            <div style="margin-top: 15px;">
                <p><strong>Date Taken:</strong> <span id="modalDate"></span></p>
                <p><strong>Notes:</strong> <span id="modalNotes"></span></p>
            </div>
        </div>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById('headerNav').classList.toggle('active');
        }

        function viewImage(src, date, notes) {
            document.getElementById('modalImage').src = src;
            document.getElementById('modalDate').textContent = date;
            document.getElementById('modalNotes').textContent = notes || 'No notes';
            document.getElementById('imageModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('imageModal').classList.remove('active');
        }

        // Close modal on outside click
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
