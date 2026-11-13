<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Smart Canteen</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
<link rel="stylesheet" href="assets/css/pages/admin_statistics.css">
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
        const dishNames = <?php echo js($dishNames); ?>;
        const quantities = <?php echo js($quantities); ?>;
        
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