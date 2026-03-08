<?php
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/db_connect.php';
    
    $email = sanitize($_POST['email']);
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Delete any existing tokens for this email
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);
            
            // Insert new token
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires_at]);
            
            // In production, send email here
            // For demo, we'll show the reset link
            $reset_link = "reset_password.php?token=" . $token;
            
            $success = 'Password reset instructions have been sent to your email.';
        } else {
            // Don't reveal if email exists or not for security
            $success = 'If this email is registered, you will receive password reset instructions.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - POSO</title>
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
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <div class="auth-links">
                    <a href="login.php" class="btn btn-primary">Back to Login</a>
                </div>
            <?php else: ?>
                <p style="text-align: center; margin-bottom: 20px; color: #666;">
                    Enter your email address and we'll send you instructions to reset your password.
                </p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required autofocus>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Reset Link</button>
                </form>
                
                <div class="auth-links">
                    <p>Remember your password? <a href="login.php">Login</a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
