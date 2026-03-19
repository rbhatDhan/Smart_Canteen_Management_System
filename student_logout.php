<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['confirm_logout'])) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

if (isset($_POST['cancel_logout'])) {
    header("Location: student_dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="modern-style.css">
    <style>
        .logout-page {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .logout-card {
            background: white;
            border-radius: 2rem;
            padding: 3rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow-lg);
            animation: fadeIn 0.6s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .logout-icon {
            width: 100px;
            height: 100px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: white;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(13,148,136,0.7); }
            70% { box-shadow: 0 0 0 20px rgba(13,148,136,0); }
            100% { box-shadow: 0 0 0 0 rgba(13,148,136,0); }
        }
        .logout-card h2 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        .logout-card p {
            color: var(--gray);
            margin-bottom: 2rem;
        }
        .user-info {
            background: var(--primary-soft);
            padding: 1rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        .user-info .name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-dark);
        }
        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        .btn {
            flex: 1;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-yes {
            background: var(--primary);
            color: white;
        }
        .btn-yes:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        .btn-no {
            background: #e2e8f0;
            color: var(--gray);
        }
        .btn-no:hover {
            background: #cbd5e1;
            transform: translateY(-2px);
        }
        @media (max-width: 480px) {
            .logout-card {
                padding: 2rem;
            }
            .button-group {
                flex-direction: column;
            }
        }
    </style>
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
                <div class="name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                <div style="color: var(--gray); font-size: 0.9rem;"><?php echo htmlspecialchars($_SESSION['usn'] ?? ''); ?></div>
            </div>

            <form method="POST">
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