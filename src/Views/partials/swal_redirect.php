<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: <?php echo js($icon); ?>,
            title: <?php echo js($title); ?>,
            text: <?php echo js($text); ?>,
            <?php if (!empty($timer)): ?>timer: <?php echo (int) $timer; ?>,
            showConfirmButton: false<?php else: ?>confirmButtonColor: "#0d9488"<?php endif; ?>

        }).then(() => {
            window.location.href = <?php echo js($redirect); ?>;
        });
    </script>
</body>
</html>
