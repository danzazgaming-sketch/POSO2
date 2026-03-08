<?php
require_once 'includes/functions.php';
requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['image_data'])) {
    require_once 'includes/db_connect.php';
    
    $image_data = $_POST['image_data'];
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Remove base64 prefix
    $image_data = str_replace('data:image/jpeg;base64,', '', $image_data);
    $image_data = str_replace('data:image/png;base64,', '', $image_data);
    $image_data = str_replace(' ', '+', $image_data);
    
    // Decode base64
    $decoded_image = base64_decode($image_data);
    
    if ($decoded_image) {
        // Generate filename with timestamp
        $filename = generateFilename('photo_') . '.jpg';
        $user_folder = getUserFolder($_SESSION['user_id']);
        $filepath = $user_folder . '/photos/' . $filename;
        
        // Save image
        if (file_put_contents($filepath, $decoded_image)) {
            // Save to database
            $stmt = $pdo->prepare("INSERT INTO photos (user_id, filename, filepath, notes) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$_SESSION['user_id'], $filename, $filepath, $notes])) {
                $success = 'Photo saved successfully!';
            } else {
                $error = 'Failed to save photo record.';
            }
        } else {
            $error = 'Failed to save image file.';
        }
    } else {
        $error = 'Invalid image data.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Picture - POSO</title>
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
            <div class="camera-section">
                <h2 style="color: var(--primary-green); margin-bottom: 10px;">📸 Take Picture</h2>
                <p style="color: #666; margin-bottom: 25px;">Capture photos with automatic timestamp</p>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="camera-container">
                    <video id="video" autoplay playsinline></video>
                    <canvas id="canvas"></canvas>
                    
                    <div class="preview-container" id="previewContainer" style="display: none;">
                        <h4 style="color: var(--primary-green); margin-bottom: 10px;">Preview:</h4>
                        <img id="preview" alt="Preview">
                    </div>
                    
                    <div class="camera-controls">
                        <button type="button" class="btn btn-primary" id="startBtn" onclick="startCamera()">
                            Start Camera
                        </button>
                        <button type="button" class="btn btn-gold" id="captureBtn" onclick="capturePhoto()" style="display: none;">
                            📸 Capture
                        </button>
                        <button type="button" class="btn btn-secondary" id="retakeBtn" onclick="retakePhoto()" style="display: none;">
                            Retake
                        </button>
                    </div>
                    
                    <form method="POST" action="" id="saveForm" style="display: none; margin-top: 20px;">
                        <input type="hidden" name="image_data" id="imageData">
                        <div class="form-group">
                            <label for="notes">Notes (Optional)</label>
                            <textarea id="notes" name="notes" rows="3" placeholder="Add notes about this photo..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Photo</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> POSO - Public Order and Safety Office | City of San Carlos, Pangasinan</p>
        </footer>
    </div>

    <script>
        let video = document.getElementById('video');
        let canvas = document.getElementById('canvas');
        let preview = document.getElementById('preview');
        let previewContainer = document.getElementById('previewContainer');
        let startBtn = document.getElementById('startBtn');
        let captureBtn = document.getElementById('captureBtn');
        let retakeBtn = document.getElementById('retakeBtn');
        let saveForm = document.getElementById('saveForm');
        let imageData = document.getElementById('imageData');
        let stream = null;

        function toggleMenu() {
            document.getElementById('headerNav').classList.toggle('active');
        }

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        facingMode: 'environment',
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }, 
                    audio: false 
                });
                video.srcObject = stream;
                video.style.display = 'block';
                canvas.style.display = 'none';
                previewContainer.style.display = 'none';
                
                startBtn.style.display = 'none';
                captureBtn.style.display = 'inline-block';
                retakeBtn.style.display = 'none';
                saveForm.style.display = 'none';
            } catch (err) {
                alert('Error accessing camera: ' + err.message);
            }
        }

        function capturePhoto() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            // Add timestamp overlay
            let ctx = canvas.getContext('2d');
            ctx.font = 'bold 24px Arial';
            ctx.fillStyle = 'white';
            ctx.strokeStyle = 'black';
            ctx.lineWidth = 2;
            let timestamp = new Date().toLocaleString();
            ctx.strokeText(timestamp, 20, canvas.height - 30);
            ctx.fillText(timestamp, 20, canvas.height - 30);
            
            // Show preview
            preview.src = canvas.toDataURL('image/jpeg', 0.9);
            previewContainer.style.display = 'block';
            
            // Stop camera
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            
            video.style.display = 'none';
            captureBtn.style.display = 'none';
            retakeBtn.style.display = 'inline-block';
            saveForm.style.display = 'block';
            imageData.value = canvas.toDataURL('image/jpeg', 0.9);
        }

        function retakePhoto() {
            startCamera();
        }

        // Auto-start camera on page load for mobile convenience
        window.onload = function() {
            if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                startCamera();
            }
        };
    </script>
</body>
</html>
