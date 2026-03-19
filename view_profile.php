<?php
session_start();

if (!isset($_SESSION['username'])) {
    echo "<h3>Please login first to view your profile.</h3>";
    exit;
}

$conn = new mysqli("localhost", "root", "", "smartcanteen","3307");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];

$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h3>User not found!</h3>";
    exit;
}

$row = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="modern-style.css">
    <style>
        .profile-page {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .profile-container {
            max-width: 600px;
            width: 100%;
        }
        .profile-card {
            background: white;
            border-radius: 2rem;
            padding: 3rem;
            box-shadow: var(--shadow-lg);
            text-align: center;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: white;
            box-shadow: 0 10px 20px rgba(13,148,136,0.3);
        }
        .profile-name {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        .profile-role {
            display: inline-block;
            padding: 0.25rem 1rem;
            background: var(--primary-soft);
            color: var(--primary);
            border-radius: 2rem;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }
        .info-grid {
            display: grid;
            gap: 1rem;
            margin: 2rem 0;
        }
        .info-item {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
        }
        .info-item:hover {
            transform: translateX(5px);
            background: var(--primary-soft);
        }
        .info-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.2rem;
        }
        .info-content {
            flex: 1;
            text-align: left;
        }
        .info-label {
            font-size: 0.8rem;
            color: var(--gray);
            margin-bottom: 0.2rem;
        }
        .info-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }
        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
        @media (max-width: 768px) {
            .profile-card {
                padding: 2rem;
            }
            .profile-name {
                font-size: 1.5rem;
            }
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="profile-page">
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-avatar">
                    <i class="fas fa-user-graduate"></i>
                </div>
                
                <h1 class="profile-name"><?php echo htmlspecialchars($row['username']); ?></h1>
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
                            <div class="info-value"><?php echo htmlspecialchars($row['email'] ?? 'Not provided'); ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-graduation-cap"></i></div>
                        <div class="info-content">
                            <div class="info-label">USN</div>
                            <div class="info-value"><?php echo htmlspecialchars($row['usn'] ?? '-'); ?></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="fas fa-building"></i></div>
                        <div class="info-content">
                            <div class="info-label">Department</div>
                            <div class="info-value"><?php echo htmlspecialchars($row['dept'] ?? '-'); ?></div>
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