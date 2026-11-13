<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
<link rel="stylesheet" href="assets/css/pages/student_dashboard.css">
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

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                <span>SMART CANTEEN</span>
            </div>
            <ul class="nav-links">
                <li><a href="#home" class="active">Dashboard</a></li>
                <li><a href="view_menu.php">Menu</a></li>
                <li><a href="place_order.php">Order</a></li>
                <li><a href="order_history.php">History</a></li>
                <li>
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($username, 0, 1)); ?>
                        </div>
                        <span><?php echo e($username); ?></span>
                        <a href="student_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section with Image and Lighter Overlay -->
    <section id="home" class="hero-section">
        <img src="assets/img/CanteenPic.jpeg" alt="Canteen" class="hero-image" onerror="this.src='https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80'">
        <div class="hero-overlay">
            <div class="hero-content">
                <h1>Welcome back, <span><?php echo e($username); ?></span>! 👋</h1>
                <p>Manage your meals and track your orders from here.</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-info">
                    <h3>Total Orders</h3>
                    <div class="number"><?php echo $totalOrders; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3>Pending</h3>
                    <div class="number"><?php echo $pendingOrders; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-utensils"></i></div>
                <div class="stat-info">
                    <h3>Menu Items</h3>
                    <div class="number"><?php echo (int) $menuCount; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div class="stat-info">
                    <h3>Rating</h3>
                    <div class="number"><?php echo $rating['average'] !== null ? number_format($rating['average'], 1) : '–'; ?></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="action-grid">
            <a href="view_menu.php" class="action-card">
                <i class="fas fa-utensils"></i>
                <h3>View Menu</h3>
                <p>Browse all available dishes</p>
            </a>
            <a href="place_order.php" class="action-card">
                <i class="fas fa-shopping-cart"></i>
                <h3>Place Order</h3>
                <p>Order your favorite food</p>
            </a>
            <a href="order_history.php" class="action-card">
                <i class="fas fa-history"></i>
                <h3>Order History</h3>
                <p>Track your past orders</p>
            </a>
            <a href="view_profile.php" class="action-card">
                <i class="fas fa-user"></i>
                <h3>My Profile</h3>
                <p>View and edit profile</p>
            </a>
        </div>

        <!-- Recent Orders Section -->
        <section id="orders" class="recent-section">
            <div class="section-header">
                <h2><i class="fas fa-history" style="color: #0d9488;"></i> Recent Orders</h2>
                <a href="order_history.php" class="view-all-btn">View All</a>
            </div>
            
            <?php if($recentOrders): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo e($order['number']); ?></td>
                                <td><?php echo e($order['label']); ?></td>
                                <td><?php echo (int) $order['quantity']; ?></td>
                                <td><?php echo date('d M, h:i A', strtotime($order['order_time'])); ?></td>
                                <td><span class="status <?php echo strtolower(e($order['status'])); ?>"><?php echo e($order['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #64748b; padding: 2rem;">No orders yet. Place your first order!</p>
            <?php endif; ?>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div>
                    <div class="footer-logo">
                        <i class="fas fa-utensils"></i>
                        <span>SMART CANTEEN</span>
                    </div>
                    <p>Your one-stop solution for campus dining management.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#home">Dashboard</a></li>
                        <li><a href="view_menu.php">Menu</a></li>
                        <li><a href="place_order.php">Order</a></li>
                        <li><a href="order_history.php">History</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="#">Help</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                    </ul>
                </div>
            <!--</div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Smart Canteen. All rights reserved.</p>
            </div>
        </div>-->
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if(target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>