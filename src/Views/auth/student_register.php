<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="assets/css/pages/student_register.css">
</head>
<body>
    <div class="register-page">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <i class="fas fa-user-plus"></i>
                    <h2>Create Account</h2>
                    <p>Join Smart Canteen today</p>
                </div>

                <?php if($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo e($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?php echo \SmartCanteen\Core\Csrf::field(); ?>
                    <div class="form-group">
                        <label>Full Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" placeholder="Enter your name" minlength="3" maxlength="50" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="email@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>USN</label>
                        <div class="usn-wrapper">
                            <div class="usn-prefix">
                                <i class="fas fa-id-card"></i>
                                4GW
                            </div>
                            <div class="input-group" style="flex: 1;">
                                <i class="fas fa-hashtag"></i>
                                <input type="text" name="usn" placeholder="23IS056" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <div class="input-group">
                            <i class="fas fa-building"></i>
                            <input type="text" name="dept" placeholder="e.g., Computer Science" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Create a password" minlength="8" maxlength="72" required>
                        </div>
                    </div>

                    <button type="submit" class="register-btn">Register</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="student_login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>
<?php if (!empty($registered)): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Registration Successful!',
            text: 'You can now login to your account.',
            timer: 3000,
            showConfirmButton: true
        }).then(() => {
            window.location.href='student_login.php';
        });
    </script>
    <?php endif; ?>
</body>
</html>