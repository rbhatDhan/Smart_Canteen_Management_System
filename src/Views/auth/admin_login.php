<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
<link rel="stylesheet" href="assets/css/pages/admin_login.css">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-user-tie"></i>
                <h2>Admin Login</h2>
                <p>Access the admin dashboard</p>
            </div>

            <?php if($error !== null): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo e($error); ?>
                </div>
            <?php endif; ?>

            <form action="admin_login_process.php" method="POST">
                <?php echo \SmartCanteen\Core\Csrf::field(); ?>
                <div class="form-group">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>

            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </div>
    </div>
</body>
</html>