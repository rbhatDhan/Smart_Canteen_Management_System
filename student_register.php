<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db_connection.php';

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $usn = trim($_POST['usn']);
    $dept = trim($_POST['dept']);
    $role = "student";

    if (empty($username) || empty($password) || empty($email) || empty($usn) || empty($dept)) {
        $error = "All fields are required.";
    } elseif (!preg_match("/\.com$/", $email)) {
        $error = "Email must end with .com";
    } else {
        if (!str_starts_with(strtoupper($usn), '4GW')) {
            $usn = '4GW' . strtoupper($usn);
        }

        $stmt_check = $conn->prepare("SELECT id FROM users WHERE usn = ? OR email = ?");
        $stmt_check->bind_param("ss", $usn, $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error = "USN or Email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role, usn, dept) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $email, $hashed_password, $role, $usn, $dept);

            if ($stmt->execute()) {
                echo "<script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        text: 'You can now login to your account.',
                        timer: 3000,
                        showConfirmButton: true
                    }).then(() => {
                        window.location.href='student_login.php';
                    });
                </script>";
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="modern-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .register-page {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .register-container {
            max-width: 500px;
            width: 100%;
        }
        .register-card {
            background: white;
            border-radius: 2rem;
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
        }
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .register-header i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .register-header h2 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .register-header p {
            color: var(--gray);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        .input-group {
            position: relative;
        }
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        .input-group input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(13,148,136,0.1);
        }
        .usn-wrapper {
            display: flex;
            gap: 0.5rem;
        }
        .usn-prefix {
            padding: 0.75rem 1rem;
            background: #f1f5f9;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            color: var(--primary);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .register-btn {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        .register-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--gray);
        }
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        @media (max-width: 640px) {
            .register-card {
                padding: 1.5rem;
            }
            .usn-wrapper {
                flex-direction: column;
            }
        }
    </style>
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

                <?php if(isset($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" placeholder="Enter your name" required>
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
                            <input type="password" name="password" placeholder="Create a password" required>
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
</body>
</html>