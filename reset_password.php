<?php
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    header('Location: login.php');
    exit();
}

require_once 'includes/db_connect.php';

// Verify token
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    $error = 'Invalid or expired reset token. Please request a new password reset.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $reset) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        // Update password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        
        if ($stmt->execute([$hashed_password, $reset['email']])) {
            // Delete reset token
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = 'Password reset successful! You can now login with your new password.';
        } else {
            $error = 'Something went wrong. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - POSO</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/logo.png">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="logo-container">
                <img src="assets/images/logo.png" alt="POSO Logo">
                <h1>POSO</h1>
                <p>Public Order and Safety Office<br>City of San Carlos, Pangasinan</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
                <div class="auth-links">
                    <a href="forgot_password.php" class="btn btn-primary">Request New Reset</a>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <div class="auth-links">
                    <a href="login.php" class="btn btn-primary">Go to Login</a>
                </div>
            <?php else: ?>
                <p style="text-align: center; margin-bottom: 20px; color: #666;">
                    Enter your new password below.
                </p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" placeholder="At least 6 characters" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
