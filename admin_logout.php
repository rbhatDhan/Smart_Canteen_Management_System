<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="modern-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }
        .logout-card {
            background: white;
            border-radius: 2rem;
            padding: 3rem;
            max-width: 450px;
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
        h2 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        p {
            color: var(--gray);
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="logout-card">
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        <h2>Logging out...</h2>
        <p>Please wait while we securely log you out.</p>
    </div>

    <script>
        Swal.fire({
            title: 'Are you sure?',
            text: 'You will be logged out of your admin account.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d9488',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                <?php
                session_unset();
                session_destroy();
                ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Logged Out!',
                    text: 'You have been successfully logged out.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = 'index.php';
                });
            } else {
                window.location.href = 'admin_dashboard.php';
            }
        });
    </script>
</body>
</html>