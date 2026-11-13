<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="assets/css/pages/admin_logout.css">
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
                fetch('admin_logout.php', { method: 'POST', headers: { 'X-CSRF-Token': <?php echo js(\SmartCanteen\Core\Csrf::token()); ?> } });
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