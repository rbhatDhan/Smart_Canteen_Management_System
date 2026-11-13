<?php
session_start();
$conn = new mysqli("localhost", "root", "", "smartcanteen", "3307");

$dishCount = $conn->query("SELECT COUNT(DISTINCT name) AS total FROM menu")->fetch_assoc()['total'] ?? 0;
$orderCount = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()['total'] ?? 0;
$pendingCount = $conn->query("SELECT COUNT(*) AS total FROM orders WHERE status='pending'")->fetch_assoc()['total'] ?? 0;
$revenue = $conn->query("
    SELECT SUM(m.price * o.quantity) AS total 
    FROM orders o 
    JOIN menu m ON o.dish_id = m.id
")->fetch_assoc()['total'] ?? 0;

$recentOrders = $conn->query("
    SELECT o.id, u.username, m.name, o.status 
    FROM orders o
    JOIN users u ON o.usn = u.usn
    JOIN menu m ON o.dish_id = m.id
    ORDER BY o.id DESC
    LIMIT 5
");
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="modern-style.css">
    <style>
        .admin-home {
            padding: 2rem;
        }
        .welcome-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            background: var(--primary-soft);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.8rem;
        }
        .stat-info h3 {
            font-size: 0.9rem;
            color: var(--gray);
        }
        .stat-info .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }
        .recent-section {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: var(--shadow);
        }
        .recent-section h2 {
            color: var(--dark);
            margin-bottom: 1.5rem;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        .orders-table th {
            text-align: left;
            padding: 1rem;
            background: var(--primary-soft);
            color: var(--primary-dark);
        }
        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .status {
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
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="admin-home">
        <div class="welcome-card">
            <h1>Welcome back, Admin! 👋</h1>
            <p>Here's what's happening in your canteen today.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-utensils"></i></div>
                <div class="stat-info">
                    <h3>Total Dishes</h3>
                    <div class="number"><?php echo $dishCount; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-info">
                    <h3>Total Orders</h3>
                    <div class="number"><?php echo $orderCount; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3>Pending</h3>
                    <div class="number"><?php echo $pendingCount; ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
                <div class="stat-info">
                    <h3>Revenue</h3>
                    <div class="number">₹<?php echo number_format($revenue, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="recent-section">
            <h2><i class="fas fa-history" style="color: var(--primary);"></i> Recent Orders</h2>
            <?php if($recentOrders && $recentOrders->num_rows > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Student</th>
                        <th>Item</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $recentOrders->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                        <td><?php echo htmlspecialchars($order['name']); ?></td>
                        <td><span class="status <?php echo strtolower($order['status']); ?>"><?php echo $order['status']; ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="text-align: center; color: var(--gray);">No orders yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>