<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
<link rel="stylesheet" href="assets/css/pages/student_logout.css">
</head>
<body>
    <div class="logout-page">
        <div class="logout-card">
            <div class="logout-icon">
                <i class="fas fa-sign-out-alt"></i>
            </div>
            <h2>Ready to Leave?</h2>
            <p>Are you sure you want to logout?</p>
            
            <div class="user-info">
                <div class="name"><?php echo e($_SESSION['username']); ?></div>
                <div style="color: var(--gray); font-size: 0.9rem;"><?php echo e($_SESSION['usn'] ?? ''); ?></div>
            </div>

            <form method="POST">
                <?php echo \SmartCanteen\Core\Csrf::field(); ?>
                <div class="button-group">
                    <button type="submit" name="confirm_logout" class="btn btn-yes">
                        <i class="fas fa-check"></i> Yes, Logout
                    </button>
                    <button type="submit" name="cancel_logout" class="btn btn-no">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>