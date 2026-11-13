<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Menu - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="assets/css/pages/update_menu.css">
</head>
<body>
    <!-- Random Rectangle Background -->
    <div class="rectangle-bg">
        <div class="rect rect-1"></div>
        <div class="rect rect-2"></div>
        <div class="rect rect-3"></div>
        <div class="rect rect-4"></div>
        <div class="rect rect-5"></div>
        <div class="rect rect-6"></div>
        <div class="rect rect-7"></div>
        <div class="rect rect-8"></div>
    </div>

    <div class="menu-page">
        <div class="menu-container">
            <!-- Back to Dashboard Button -->
            <div class="back-to-dashboard">
                <a href="admin_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-utensils" style="color: var(--primary);"></i> Update Weekly Menu</h1>
                <p>Add, edit, or remove dishes for each day</p>
            </div>

            <!-- Day Tabs -->
            <div class="day-tabs">
                <?php foreach($days as $index => $day): ?>
                    <div class="day-tab <?php echo $index === 0 ? 'active' : ''; ?>" onclick="showDay('<?php echo $day; ?>')">
                        <?php echo $day; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Menu Sections -->
            <?php foreach($days as $day): ?>
                <div id="day-<?php echo $day; ?>" class="day-section <?php echo $day === 'Monday' ? 'active' : ''; ?>">
                    <div class="day-header">
                        <h2><i class="fas fa-utensils" style="color: var(--primary);"></i> <?php echo $day; ?></h2>
                        <span class="item-count"><?php echo isset($menu[$day]) ? count($menu[$day]) : 0; ?> items</span>
                    </div>

                    <?php if(!empty($menu[$day])): ?>
                        <div class="table-responsive">
                            <table class="menu-table">
                                <thead>
                                    <tr>
                                        <th>Dish Name</th>
                                        <th>Price (₹)</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($menu[$day] as $dish): ?>
                                        <tr>
                                            <?php if($edit_id == $dish['id']): ?>
                                                <form method="POST">
                                                    <?php echo \SmartCanteen\Core\Csrf::field(); ?>
                                                    <td>
                                                        <input type="text" name="name" value="<?php echo e($dish['name']); ?>" style="padding: 0.5rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; width: 100%;" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="price" value="<?php echo $dish['price']; ?>" step="0.01" style="padding: 0.5rem; border: 2px solid #e2e8f0; border-radius: 0.5rem; width: 100%;" required>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="id" value="<?php echo $dish['id']; ?>">
                                                        <button type="submit" name="save_update" class="btn-save">
                                                            <i class="fas fa-save"></i> Save
                                                        </button>
                                                        <a href="update_menu.php" class="cancel-link">Cancel</a>
                                                    </td>
                                                </form>
                                            <?php else: ?>
                                                <td><strong><?php echo e($dish['name']); ?></strong></td>
                                                <td>₹<?php echo number_format($dish['price'], 2); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="update_menu.php?edit_id=<?php echo $dish['id']; ?>" class="btn-icon btn-edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" 
                                                           class="btn-icon btn-delete"
                                                           onclick="confirmDelete(<?php echo (int) $dish['id']; ?>, <?php echo js($dish['name']); ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-utensil-spoon"></i>
                            <p>No dishes added for <?php echo $day; ?> yet.</p>
                        </div>
                    <?php endif; ?>

                    <!-- Add New Dish Form -->
                    <form method="POST" class="add-form">
                        <?php echo \SmartCanteen\Core\Csrf::field(); ?>
                        <input type="text" name="name" placeholder="Dish Name" required>
                        <input type="number" name="price" placeholder="Price (₹)" step="0.01" required>
                        <input type="hidden" name="menu_day" value="<?php echo $day; ?>">
                        <button type="submit" name="add_menu" class="btn-add">
                            <i class="fas fa-plus-circle"></i> Add to <?php echo $day; ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = <?php echo js(\SmartCanteen\Core\Csrf::token()); ?>;

        function showDay(day) {
            document.querySelectorAll('.day-section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.day-tab').forEach(t => t.classList.remove('active'));
            document.getElementById('day-' + day).classList.add('active');
            event.target.classList.add('active');
        }

        function confirmDelete(id, dishName) {
            Swal.fire({
                title: 'Delete Dish?',
                text: `Are you sure you want to delete "${dishName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'update_menu.php';
                    [['_csrf', CSRF_TOKEN], ['delete_menu', '1'], ['delete_id', id]].forEach(([k, v]) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = k;
                        input.value = v;
                        form.appendChild(input);
                    });
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>