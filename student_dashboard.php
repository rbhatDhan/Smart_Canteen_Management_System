<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'student') {
    header("Location: student_login.php");
    exit;
}

$username = $_SESSION['username'];
$usn = $_SESSION['usn'];

$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE usn = '$usn'")->fetch_assoc()['count'] ?? 0;
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE usn = '$usn' AND status='pending'")->fetch_assoc()['count'] ?? 0;
$menuCount = $conn->query("SELECT COUNT(DISTINCT name) AS total FROM menu")->fetch_assoc()['total'] ?? 45;

// Fetch recent orders
$recentOrders = $conn->query("
    SELECT o.id, m.name, o.quantity, o.status, o.order_time 
    FROM orders o
    JOIN menu m ON o.dish_id = m.id
    WHERE o.usn = '$usn'
    ORDER BY o.order_time DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="modern-style.css">
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #14b8a6;
            --primary-soft: #ccfbf1;
            --dark: #115e59;
            --darker: #134e4a;
            --gray: #64748b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #1e293b;
            line-height: 1.6;
            overflow-x: hidden;
            background: #f8fafc;
            position: relative;
            min-height: 100vh;
        }

        /* Random Rectangle Background - More Visible */
        .rectangle-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .rect {
            position: absolute;
            background: var(--primary);
            opacity: 0.12;
            border-radius: 30px;
            box-shadow: 0 0 30px rgba(13,148,136,0.1);
        }

        .rect-1 {
            top: 5%;
            left: 2%;
            width: 350px;
            height: 180px;
            transform: rotate(12deg);
            animation: floatRect1 22s ease-in-out infinite;
        }

        .rect-2 {
            top: 55%;
            right: 3%;
            width: 450px;
            height: 220px;
            transform: rotate(-8deg);
            animation: floatRect2 26s ease-in-out infinite reverse;
        }

        .rect-3 {
            bottom: 10%;
            left: 10%;
            width: 300px;
            height: 400px;
            transform: rotate(20deg);
            animation: floatRect3 24s ease-in-out infinite;
        }

        .rect-4 {
            top: 25%;
            right: 15%;
            width: 400px;
            height: 200px;
            transform: rotate(-15deg);
            animation: floatRect4 28s ease-in-out infinite;
        }

        .rect-5 {
            bottom: 30%;
            left: 35%;
            width: 250px;
            height: 320px;
            transform: rotate(35deg);
            animation: floatRect5 25s ease-in-out infinite 1s;
        }

        .rect-6 {
            top: 70%;
            right: 25%;
            width: 380px;
            height: 190px;
            transform: rotate(-25deg);
            animation: floatRect6 27s ease-in-out infinite 0.5s;
        }

        .rect-7 {
            top: 12%;
            left: 25%;
            width: 320px;
            height: 250px;
            transform: rotate(8deg);
            animation: floatRect7 23s ease-in-out infinite 1.5s;
        }

        .rect-8 {
            bottom: 15%;
            right: 35%;
            width: 420px;
            height: 210px;
            transform: rotate(-12deg);
            animation: floatRect8 29s ease-in-out infinite 2s;
        }

        @keyframes floatRect1 {
            0%, 100% { transform: rotate(12deg) translate(0, 0); }
            50% { transform: rotate(12deg) translate(45px, -35px); }
        }

        @keyframes floatRect2 {
            0%, 100% { transform: rotate(-8deg) translate(0, 0); }
            50% { transform: rotate(-8deg) translate(-40px, 45px); }
        }

        @keyframes floatRect3 {
            0%, 100% { transform: rotate(20deg) translate(0, 0) scale(1); }
            50% { transform: rotate(20deg) translate(35px, 35px) scale(1.08); }
        }

        @keyframes floatRect4 {
            0%, 100% { transform: rotate(-15deg) translate(0, 0); }
            50% { transform: rotate(-15deg) translate(-45px, -25px); }
        }

        @keyframes floatRect5 {
            0%, 100% { transform: rotate(35deg) translate(0, 0); }
            50% { transform: rotate(35deg) translate(25px, -45px); }
        }

        @keyframes floatRect6 {
            0%, 100% { transform: rotate(-25deg) translate(0, 0); }
            50% { transform: rotate(-25deg) translate(-30px, 35px); }
        }

        @keyframes floatRect7 {
            0%, 100% { transform: rotate(8deg) translate(0, 0); }
            50% { transform: rotate(8deg) translate(35px, -25px); }
        }

        @keyframes floatRect8 {
            0%, 100% { transform: rotate(-12deg) translate(0, 0); }
            50% { transform: rotate(-12deg) translate(-45px, 25px); }
        }

        /* Navigation - Made transparent */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            z-index: 1000;
            padding: 0.8rem 0;
        }

        .navbar .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.3rem;
            font-weight: 700;
            color: #115e59;
        }

        .logo i {
            color: #0d9488;
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            gap: 1.8rem;
            align-items: center;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #475569;
            font-weight: 500;
            transition: color 0.3s;
            font-size: 1rem;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: #0d9488;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background: #0d9488;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .logout-btn {
            padding: 0.4rem 0.8rem;
            background: #fee2e2;
            color: #ef4444;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        .logout-btn:hover {
            background: #ef4444;
            color: white;
        }
        .logout-btn i {
            font-size: 0.9rem;
        }

        /* Hero Section with Image and Overlay */
        .hero-section {
            position: relative;
            width: 100vw;
            height: 450px;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            margin-top: 60px;
            margin-bottom: 2rem;
            overflow: hidden;
            z-index: 1;
        }

        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, rgba(13,148,136,0.4), rgba(15,118,110,0.3));
            display: flex;
            align-items: center;
            padding: 0 6rem;
        }

        .hero-content {
            max-width: 700px;
            color: white;
            animation: fadeInLeft 1s ease;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .hero-content h1 {
            font-size: 3.2rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            line-height: 1.2;
        }

        .hero-content h1 span {
            color: #ffd700;
        }

        .hero-content p {
            font-size: 1.2rem;
            opacity: 0.95;
            margin-bottom: 0.5rem;
        }

        /* Main Content */
        .main-content {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 2;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 3;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            position: relative;
            z-index: 3;
            border: 1px solid rgba(13,148,136,0.1);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(13,148,136,0.2);
            border-color: var(--primary);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: #ccfbf1;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0d9488;
            font-size: 1.8rem;
        }

        .stat-info h3 {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .stat-info .number {
            font-size: 2rem;
            font-weight: 700;
            color: #115e59;
        }

        /* Action Cards */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
            position: relative;
            z-index: 3;
        }

        .action-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
            position: relative;
            z-index: 3;
        }

        .action-card:hover {
            transform: translateY(-5px);
            border-color: #0d9488;
            box-shadow: 0 10px 15px -3px rgba(13,148,136,0.2);
        }

        .action-card i {
            font-size: 2.5rem;
            color: #0d9488;
            margin-bottom: 1rem;
        }

        .action-card h3 {
            color: #115e59;
            margin-bottom: 0.5rem;
        }

        .action-card p {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Recent Orders Section */
        .recent-section {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            position: relative;
            z-index: 3;
            border: 1px solid rgba(13,148,136,0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-header h2 {
            color: #115e59;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-all-btn {
            padding: 0.5rem 1rem;
            background: #0d9488;
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s;
        }

        .view-all-btn:hover {
            background: #0f766e;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            text-align: left;
            padding: 1rem;
            background: #ccfbf1;
            color: #0f766e;
            font-weight: 600;
        }

        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .orders-table tr:hover td {
            background: #f8fafc;
        }

        .status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status.completed {
            background: #d1fae5;
            color: #065f46;
        }

        /* Footer */
        .footer {
            background: #115e59;
            color: white;
            padding: 3rem 0 1.5rem;
            margin-top: 3rem;
            position: relative;
            z-index: 3;
        }

        .footer .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 2rem;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .footer-logo i {
            color: #0d9488;
        }

        .footer-links h4 {
            margin-bottom: 1rem;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: #0d9488;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid,
            .action-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .hero-section {
                height: 400px;
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                height: 350px;
                margin-top: 70px;
            }
            
            .hero-overlay {
                padding: 0 2rem;
                align-items: flex-end;
                padding-bottom: 2rem;
            }
            
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .stats-grid,
            .action-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .nav-links {
                display: none;
            }
        }
    </style>
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
                        <span><?php echo htmlspecialchars($username); ?></span>
                        <a href="student_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section with Image and Lighter Overlay -->
    <section id="home" class="hero-section">
        <img src="CanteenPic.jpeg" alt="Canteen" class="hero-image" onerror="this.src='https://images.unsplash.com/photo-1555396273-367ea4eb4db5?ixlib=rb-1.2.1&auto=format&fit=crop&w=1200&q=80'">
        <div class="hero-overlay">
            <div class="hero-content">
                <h1>Welcome back, <span><?php echo htmlspecialchars($username); ?></span>! 👋</h1>
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
                    <div class="number"><?php echo $menuCount; ?>+</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div class="stat-info">
                    <h3>Rating</h3>
                    <div class="number">4.8</div>
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
            
            <?php if($recentOrders && $recentOrders->num_rows > 0): ?>
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
                        <?php while($order = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td><?php echo $order['quantity']; ?></td>
                                <td><?php echo date('h:i A', strtotime($order['order_time'])); ?></td>
                                <td><span class="status <?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                            </tr>
                        <?php endwhile; ?>
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