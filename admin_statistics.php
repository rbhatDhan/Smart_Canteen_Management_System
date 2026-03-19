<?php
session_start();
include 'db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "smartcanteen", "3307");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "
    SELECT m.name AS dish_name, SUM(o.quantity) AS total_sold
    FROM orders o
    JOIN menu m ON o.dish_id = m.id
    GROUP BY o.dish_id
    ORDER BY total_sold DESC
";
$result = $conn->query($sql);

$dishNames = [];
$quantities = [];

while ($row = $result->fetch_assoc()) {
    $dishNames[] = $row['dish_name'];
    $quantities[] = $row['total_sold'];
}

$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'] ?? 0;
$totalRevenue = $conn->query("
    SELECT SUM(m.price * o.quantity) as total 
    FROM orders o 
    JOIN menu m ON o.dish_id = m.id
")->fetch_assoc()['total'] ?? 0;
$avgItems = $conn->query("SELECT AVG(quantity) as avg FROM orders")->fetch_assoc()['avg'] ?? 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Smart Canteen</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            padding: 2rem;
            position: relative;
            background: #f8fafc;
        }

        /* Random Rectangle Background */
        .rectangle-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            pointer-events: none;
        }

        .rect {
            position: absolute;
            background: var(--primary);
            opacity: 0.10;
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

        .stats-page {
            padding: 2rem;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        .stats-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Back Button */
        .back-to-dashboard {
            margin-bottom: 1.5rem;
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
            border: 1px solid var(--primary-soft);
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-5px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.2);
        }

        .stats-header {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border: 1px solid rgba(13,148,136,0.1);
        }

        .stats-header h1 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .stats-header p {
            color: var(--gray);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            border: 1px solid rgba(13,148,136,0.1);
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(13,148,136,0.2);
            border-color: var(--primary);
        }

        .summary-icon {
            width: 60px;
            height: 60px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .summary-icon.orders { 
            background: #dbeafe; 
            color: #3b82f6; 
        }
        .summary-icon.revenue { 
            background: #dcfce7; 
            color: #22c55e; 
        }
        .summary-icon.avg { 
            background: #fef3c7; 
            color: #f59e0b; 
        }

        .summary-info h3 {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }

        .summary-info .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .chart-container {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(13,148,136,0.1);
        }

        .chart-container h2 {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        canvas {
            max-height: 400px;
            width: 100% !important;
        }

        @media (max-width: 1024px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-header h1 {
                font-size: 1.5rem;
            }
            
            .stats-page {
                padding: 1rem;
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

    <div class="stats-page">
        <div class="stats-container">
            <!-- Back to Dashboard Button -->
            <div class="back-to-dashboard">
                <a href="admin_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <!-- Page Header -->
            <div class="stats-header">
                <h1><i class="fas fa-chart-bar" style="color: var(--primary);"></i> Canteen Statistics</h1>
                <p>Track your canteen's performance and popular dishes</p>
            </div>

            <!-- Summary Cards -->
            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-icon orders"><i class="fas fa-shopping-cart"></i></div>
                    <div class="summary-info">
                        <h3>Total Orders</h3>
                        <div class="number"><?php echo number_format($totalOrders); ?></div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon revenue"><i class="fas fa-rupee-sign"></i></div>
                    <div class="summary-info">
                        <h3>Total Revenue</h3>
                        <div class="number">₹<?php echo number_format($totalRevenue, 2); ?></div>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon avg"><i class="fas fa-chart-line"></i></div>
                    <div class="summary-info">
                        <h3>Avg Items/Order</h3>
                        <div class="number"><?php echo number_format($avgItems, 1); ?></div>
                    </div>
                </div>
            </div>

            <!-- Chart Container -->
            <div class="chart-container">
                <h2><i class="fas fa-chart-bar" style="color: var(--primary);"></i> Dish Popularity</h2>
                <canvas id="itemChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const dishNames = <?php echo json_encode($dishNames); ?>;
        const quantities = <?php echo json_encode($quantities); ?>;
        
        const colors = [
            '#0d9488', '#14b8a6', '#2dd4bf', '#3b82f6', '#8b5cf6',
            '#ec4899', '#f59e0b', '#f97316', '#ef4444', '#84cc16'
        ];

        new Chart(document.getElementById('itemChart'), {
            type: 'bar',
            data: {
                labels: dishNames,
                datasets: [{
                    label: 'Quantity Sold',
                    data: quantities,
                    backgroundColor: dishNames.map((_, i) => colors[i % colors.length]),
                    borderRadius: 8,
                    barPercentage: 0.7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0d9488',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0' },
                        title: {
                            display: true,
                            text: 'Quantity Sold',
                            color: '#64748b',
                            font: { weight: 600 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        title: {
                            display: true,
                            text: 'Dish Name',
                            color: '#64748b',
                            font: { weight: 600 }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>