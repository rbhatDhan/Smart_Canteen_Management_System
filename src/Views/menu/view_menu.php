<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Menu - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/pages/view_menu.css">
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

    <div class="container">
        <!-- Back Button Section - Conditional based on login -->
        <div class="back-section">
            <?php if(isset($_SESSION['username'])): ?>
                <!-- Logged in - Show Back to Home and Dashboard -->
                <a href="index.php" class="back-btn">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="<?php echo $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'; ?>" class="dashboard-btn">
                    <i class="fas fa-tachometer-alt"></i> Back to Dashboard
                </a>
            <?php else: ?>
                <!-- Not logged in - Only show Back to Home -->
                <a href="index.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Home
                </a>
            <?php endif; ?>
        </div>

        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-utensils" style="color: var(--primary);"></i> Weekly Menu</h1>
            <p>Discover our delicious offerings for each day</p>
        </div>

        <?php if (!empty($menu)): ?>
            <!-- Day Tabs -->
            <div class="day-tabs">
                <?php foreach($days as $day): ?>
                    <div class="day-tab <?php echo $day === $today ? 'today' : ''; ?> <?php echo $day === 'Monday' ? 'active' : ''; ?>" 
                         onclick="showDay('<?php echo $day; ?>')">
                        <?php echo $day; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Menu Sections -->
            <?php foreach($days as $day): ?>
                <div id="day-<?php echo $day; ?>" class="menu-section <?php echo $day === 'Monday' ? 'active' : ''; ?>">
                    <div class="day-header">
                        <i class="fas fa-<?php 
                            echo $day === 'Monday' ? 'sun' : 
                                ($day === 'Tuesday' ? 'cloud-sun' : 
                                ($day === 'Wednesday' ? 'cloud' : 
                                ($day === 'Thursday' ? 'cloud-rain' : 
                                ($day === 'Friday' ? 'umbrella' : 
                                ($day === 'Saturday' ? 'wine-glass-alt' : 'cocktail'))))); 
                        ?>"></i>
                        <h2><?php echo $day; ?></h2>
                    </div>
                    
                    <?php if(!empty($menu[$day])): ?>
                        <div class="menu-items">
                            <?php foreach($menu[$day] as $item): ?>
                                <div class="menu-item">
                                    <div class="item-info">
                                        <h3><?php echo e($item['name']); ?></h3>
                                        <p>Freshly prepared • Vegetarian</p>
                                    </div>
                                    <div class="item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-utensil-spoon"></i>
                            <p>No items available for <?php echo $day; ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Order Button -->
            <?php if(isset($_SESSION['username'])): ?>
                <a href="place_order.php" class="order-btn">
                    Place Your Order <i class="fas fa-arrow-right"></i>
                </a>
            <?php else: ?>
                <a href="javascript:void(0);" onclick="alert('Please login to place an order!')" class="order-btn" style="background: var(--gray);">
                    Login to Order <i class="fas fa-lock"></i>
                </a>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state" style="background: white; padding: 4rem; border-radius: 30px;">
                <i class="fas fa-utensil-spoon"></i>
                <h3 style="color: var(--dark); margin-bottom: 0.5rem;">No Menu Available</h3>
                <p style="color: var(--gray);">Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function showDay(day) {
            // Hide all sections
            document.querySelectorAll('.menu-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show selected day
            document.getElementById('day-' + day).classList.add('active');
            
            // Update tabs
            document.querySelectorAll('.day-tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.textContent.trim() === day) {
                    tab.classList.add('active');
                }
            });
        }

        // Auto-select today
        const today = '<?php echo $today; ?>';
        if (document.getElementById('day-' + today)) {
            showDay(today);
        }
    </script>
</body>
</html>