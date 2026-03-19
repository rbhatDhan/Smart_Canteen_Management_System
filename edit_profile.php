<?php
session_start();

$conn = new mysqli("localhost", "root", "", "smartcanteen","3307");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['username'])) {
    echo "<script>alert('Please login first!'); window.location='student_login.php';</script>";
    exit;
}

$username = $_SESSION['username'];

$sql = "SELECT username, email, dept, usn FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('User not found!'); window.location='view_profile.php';</script>";
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

if (isset($_POST['update'])) {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];
    $new_dept = $_POST['dept'];
    $new_usn = $_POST['usn'];

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET username=?, email=?, password=?, dept=?, usn=? WHERE username=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssss", $new_username, $new_email, $hashed_password, $new_dept, $new_usn, $username);
    } else {
        $update_sql = "UPDATE users SET username=?, email=?, dept=?, usn=? WHERE username=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssss", $new_username, $new_email, $new_dept, $new_usn, $username);
    }

    if ($stmt->execute()) {
        $_SESSION['username'] = $new_username;
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Profile updated successfully!',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location='view_profile.php';
            });
        </script>";
        exit;
    } else {
        $error = "Error updating profile!";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="modern-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .edit-page {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .edit-container {
            max-width: 600px;
            width: 100%;
        }
        .edit-card {
            background: white;
            border-radius: 2rem;
            padding: 3rem;
            box-shadow: var(--shadow-lg);
        }
        .edit-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .edit-header i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        .edit-header h1 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        .edit-header p {
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
        .password-hint {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 0.25rem;
            margin-left: 2.5rem;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
        }
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        @media (max-width: 768px) {
            .edit-card {
                padding: 2rem;
            }
            .edit-header h1 {
                font-size: 1.5rem;
            }
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
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

                <?php if(isset($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>USN</label>
                        <div class="input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" name="usn" value="<?php echo htmlspecialchars($user['usn']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <div class="input-group">
                            <i class="fas fa-building"></i>
                            <input type="text" name="dept" value="<?php echo htmlspecialchars($user['dept']); ?>" required>
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
</body>
</html>