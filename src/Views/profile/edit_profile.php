<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="assets/css/pages/edit_profile.css">
</head>
<body>
    <div class="edit-page">
        <div class="edit-container">
            <div class="edit-card">
                <div class="edit-header">
                    <i class="fas fa-user-edit"></i>
                    <h1>Edit Profile</h1>
                    <p>Update your personal information</p>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo e($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?php echo \SmartCanteen\Core\Csrf::field(); ?>
                    <div class="form-group">
                        <label>Username</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" value="<?php echo e($user['username']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>USN</label>
                        <div class="input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" name="usn" value="<?php echo e($user['usn']); ?>" <?php echo ($user['role'] ?? '') === 'admin' ? '' : 'required'; ?>>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" value="<?php echo e($user['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <div class="input-group">
                            <i class="fas fa-building"></i>
                            <input type="text" name="dept" value="<?php echo e($user['dept']); ?>" <?php echo ($user['role'] ?? '') === 'admin' ? '' : 'required'; ?>>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>New Password (leave blank to keep current)</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Enter new password">
                        </div>
                        <div class="password-hint">
                            <i class="fas fa-info-circle"></i> Leave empty to keep your current password
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" name="update" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                        <a href="view_profile.php" class="btn btn-outline">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php if (!empty($updated)): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'Profile updated successfully!',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location='view_profile.php';
        });
    </script>
    <?php endif; ?>
</body>
</html>