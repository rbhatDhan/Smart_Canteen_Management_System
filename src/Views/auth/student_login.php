<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
<link rel="stylesheet" href="assets/css/pages/student_login.css">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-user-graduate"></i>
                <h2>Student Login</h2>
                <p>Access your canteen account</p>
            </div>

            <?php if(!empty($msg)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo e($msg); ?>
                </div>
            <?php endif; ?>

            <?php if($error !== null): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form action="student_login_process.php" method="POST">
                <?php echo \SmartCanteen\Core\Csrf::field(); ?>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username or USN" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
                <div class="forgot-link"><a href="forgotPassword/forgot_password.php">Forgot password?</a></div>
            </form>

            <div class="register-link">
                New user? <a href="student_register.php">Register here</a>
            </div>
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>
</body>
</html>