<?php
include 'db_connection.php';
session_start();

if (!isset($_SESSION['usn'])) {
    header("Location: student_login.php");
    exit();
}

$usn = $_SESSION['usn'];

$stmt = $conn->prepare("SELECT username FROM users WHERE usn = ?");
$stmt->bind_param("s", $usn);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p>User not found.</p>";
    exit();
}

$username = $user['username'];

$query = "SELECT o.id, m.name AS dish_name, o.quantity, o.order_time, o.status, o.payment_status
          FROM orders o
          JOIN menu m ON o.dish_id = m.id
          WHERE o.usn = ? AND o.status = 'Completed'
          ORDER BY o.order_time DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $usn);
$stmt->execute();
$orders = $stmt->get_result();

$totalOrders = $orders->num_rows;
$favoriteDish = [];

$orders->data_seek(0);
while ($row = $orders->fetch_assoc()) {
    if (!isset($favoriteDish[$row['dish_name']])) {
        $favoriteDish[$row['dish_name']] = 0;
    }
    $favoriteDish[$row['dish_name']] += $row['quantity'];
}
$orders->data_seek(0);

$favoriteDish = array_keys($favoriteDish, max($favoriteDish))[0] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Smart Canteen</title>
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

        /* Random Rectangle Background */
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

        .history-page {
            padding: 2rem;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        .history-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Back to Dashboard Button */
        .back-section {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: white;
            color: var(--primary);
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border: 1px solid rgba(13,148,136,0.2);
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-5px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.2);
            border-color: var(--primary);
        }

        .dashboard-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .dashboard-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.3);
        }

        .history-card {
            background: white;
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
            border: 1px solid rgba(13,148,136,0.1);
            position: relative;
            z-index: 2;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .user-greeting {
            color: var(--gray);
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--primary-soft);
            padding: 1.5rem;
            border-radius: 1rem;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid rgba(13,148,136,0.2);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.15);
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .orders-table-container {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            text-align: left;
            padding: 1rem;
            background: var(--primary);
            color: white;
            font-weight: 600;
        }

        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .orders-table tr:hover td {
            background: var(--primary-soft);
        }

        .order-id {
            font-weight: 600;
            color: var(--primary);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
            background: #d1fae5;
            color: #065f46;
        }

        .payment-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
            background: #dcfce7;
            color: #166534;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--gray);
            margin-bottom: 2rem;
        }

        .order-btn {
            display: inline-block;
            padding: 0.75rem 2rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .order-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.3);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .history-card {
                padding: 1.5rem;
            }
            .page-header h1 {
                font-size: 1.5rem;
            }
            .back-section {
                flex-direction: column;
            }
            .back-btn, .dashboard-btn {
                width: 100%;
                justify-content: center;
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

    <div class="history-page">
        <div class="history-container">
            <!-- Back to Dashboard Button -->
            <div class="back-section">
                <a href="student_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="place_order.php" class="dashboard-btn">
                    <i class="fas fa-shopping-cart"></i> Place New Order
                </a>
            </div>

            <div class="history-card">
                <div class="page-header">
                    <h1><i class="fas fa-history" style="color: var(--primary);"></i> Order History</h1>
                    <div class="user-greeting">Welcome back, <?php echo htmlspecialchars($username); ?>!</div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stat-value"><?php echo $totalOrders; ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-star"></i></div>
                        <div class="stat-value"><?php echo htmlspecialchars($favoriteDish); ?></div>
                        <div class="stat-label">Favorite Dish</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                        <div class="stat-value">2024</div>
                        <div class="stat-label">Member Since</div>
                    </div>
                </div>

                <?php if($orders->num_rows > 0): ?>
                    <div class="orders-table-container">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Dish Name</th>
                                    <th>Quantity</th>
                                    <th>Order Time</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $orders->fetch_assoc()): ?>
                                    <tr>
                                        <td class="order-id">#<?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['dish_name']); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                        <td><?php echo date('d M Y, h:i A', strtotime($row['order_time'])); ?></td>
                                        <td><span class="status-badge"><?php echo $row['status']; ?></span></td>
                                        <td><span class="payment-badge"><?php echo $row['payment_status']; ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <h3>No Orders Yet</h3>
                        <p>You haven't placed any orders yet. Start ordering your favorite meals!</p>
                        <a href="place_order.php" class="order-btn">
                            <i class="fas fa-utensils"></i> Order Now
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>