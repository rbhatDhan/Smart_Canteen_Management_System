<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
<link rel="stylesheet" href="assets/css/pages/view_profile.css">
</head>
<body>
    <div class="profile-page">
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-avatar">
                    <i class="fas fa-user-graduate"></i>
                </div>
                
                <h1 class="profile-name"><?php echo e($row['username']); ?></h1>
                <div class="profile-role">
                    <i class="fas fa-<?php echo $row['role'] === 'admin' ? 'crown' : 'user'; ?>"></i>
                    <?php echo ucfirst($row['role']); ?>
                </div>

                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-id-card"></i></div>
                        <div class="info-content">
                            <div class="info-label">User ID</div>
                            <div class="info-value">#<?php echo $row['id']; ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-envelope"></i></div>
                        <div class="info-content">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo e($row['email'] ?? 'Not provided'); ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-graduation-cap"></i></div>
                        <div class="info-content">
                            <div class="info-label">USN</div>
                            <div class="info-value"><?php echo e($row['usn'] ?? '-'); ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-building"></i></div>
                        <div class="info-content">
                            <div class="info-label">Department</div>
                            <div class="info-value"><?php echo e($row['dept'] ?? '-'); ?></div>
                        </div>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="edit_profile.php" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                    <a href="<?php echo $row['role'] === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'; ?>" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>