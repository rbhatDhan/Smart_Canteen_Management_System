<?php
include 'db_connection.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="modern-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .orders-page {
            padding: 2rem;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        .orders-container {
            max-width: 1400px;
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

        .page-header {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border: 1px solid rgba(13,148,136,0.1);
        }

        .page-header h1 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: var(--gray);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid rgba(13,148,136,0.1);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(13,148,136,0.2);
            border-color: var(--primary);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .stat-icon.total { 
            background: #dbeafe; 
            color: #3b82f6; 
        }
        .stat-icon.pending { 
            background: #fef3c7; 
            color: #f59e0b; 
        }
        .stat-icon.completed { 
            background: #dcfce7; 
            color: #22c55e; 
        }

        .stat-info h3 {
            font-size: 0.9rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }

        .stat-info .number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .filter-section {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            border: 1px solid rgba(13,148,136,0.1);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .filter-select {
            padding: 0.6rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            min-width: 150px;
            background: white;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .search-box {
            position: relative;
            flex: 1;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(13,148,136,0.1);
        }

        .refresh-btn {
            padding: 0.6rem 1.2rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            font-weight: 500;
        }

        .refresh-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.2);
        }

        .orders-table-container {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            border: 1px solid rgba(13,148,136,0.1);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            text-align: left;
            padding: 1rem;
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 0.95rem;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            color: var(--dark);
        }

        .table tbody tr:hover td {
            background: #f8fafc;
        }

        .status-select {
            padding: 0.4rem 0.75rem;
            border-radius: 2rem;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .status-select.Pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-select.Completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-select:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .payment-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .payment-badge.Successful {
            background: #dcfce7;
            color: #166534;
        }

        .payment-badge.Unsuccessful {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-section {
                flex-direction: column;
            }
            
            .filter-select, 
            .search-box, 
            .refresh-btn {
                width: 100%;
            }
            
            .orders-page {
                padding: 1rem;
            }
            
            .table th, 
            .table td {
                padding: 0.75rem;
                font-size: 0.9rem;
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

    <div class="orders-page">
        <div class="orders-container">
            <!-- Back to Dashboard Button -->
            <div class="back-to-dashboard">
                <a href="admin_dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-shopping-cart" style="color: var(--primary);"></i> Order Management</h1>
                <p>Track and manage all customer orders</p>
            </div>

            <?php
            $totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'] ?? 0;
            $pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Pending'")->fetch_assoc()['count'] ?? 0;
            $completedOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status='Completed'")->fetch_assoc()['count'] ?? 0;
            ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon total"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <div class="number"><?php echo $totalOrders; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <h3>Pending</h3>
                        <div class="number"><?php echo $pendingOrders; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon completed"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <h3>Completed</h3>
                        <div class="number"><?php echo $completedOrders; ?></div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <select class="filter-select" id="statusFilter" onchange="filterOrders()">
                    <option value="all">All Orders</option>
                    <option value="Pending">Pending</option>
                    <option value="Completed">Completed</option>
                </select>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search by Order ID, USN, or Dish..." onkeyup="filterOrders()">
                </div>
                <button class="refresh-btn" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <!-- Orders Table -->
            <div class="orders-table-container">
                <div class="table-responsive">
                    <table class="table" id="ordersTable">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>USN</th>
                                <th>Dish</th>
                                <th>Qty</th>
                                <th>Order Time</th>
                                <th>Payment</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT o.id, u.usn, m.name AS dish_name, o.quantity, o.order_time, o.status, o.payment_status
                                    FROM orders o
                                    JOIN users u ON o.usn = u.usn
                                    JOIN menu m ON o.dish_id = m.id
                                    ORDER BY o.id DESC";
                            $result = $conn->query($sql);

                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $orderId = $row['id'];
                                    $status = htmlspecialchars($row['status']);
                                    $payment = htmlspecialchars($row['payment_status']);

                                    if ($payment == 'Unsuccessful') {
                                        $status = 'Pending';
                                        $disabled = 'disabled';
                                    } else {
                                        $disabled = '';
                                    }

                                    echo "<tr data-status='{$status}' data-search='" . strtolower($orderId . ' ' . $row['usn'] . ' ' . $row['dish_name']) . "'>
                                            <td><strong>#{$orderId}</strong></td>
                                            <td>{$row['usn']}</td>
                                            <td>{$row['dish_name']}</td>
                                            <td>{$row['quantity']}</td>
                                            <td>" . date('d M Y, h:i A', strtotime($row['order_time'])) . "</td>
                                            <td><span class='payment-badge {$payment}'>{$payment}</span></td>
                                            <td>
                                                <select onchange='updateStatus({$orderId}, this)' 
                                                        class='status-select {$status}' 
                                                        {$disabled}>
                                                    <option value='Pending' " . ($status == 'Pending' ? 'selected' : '') . ">⏳ Pending</option>
                                                    <option value='Completed' " . ($status == 'Completed' ? 'selected' : '') . ">✅ Completed</option>
                                                </select>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' style='text-align: center; padding: 3rem; color: var(--gray);'>No orders found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateStatus(orderId, selectElement) {
            const newStatus = selectElement.value;
            
            Swal.fire({
                title: 'Update Status',
                text: `Change order #${orderId} status to ${newStatus}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d9488',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, update'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('update_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'id=' + orderId + '&status=' + newStatus
                    })
                    .then(response => response.text())
                    .then(data => {
                        selectElement.className = 'status-select ' + newStatus;
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: `Order #${orderId} is now ${newStatus}`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update status'
                        });
                    });
                } else {
                    selectElement.value = selectElement.value === 'Pending' ? 'Completed' : 'Pending';
                }
            });
        }

        function filterOrders() {
            const statusFilter = document.getElementById('statusFilter').value;
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#ordersTable tbody tr');
            
            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const searchText = row.getAttribute('data-search') || '';
                
                const showByStatus = statusFilter === 'all' || status === statusFilter;
                const showBySearch = searchTerm === '' || searchText.includes(searchTerm);
                
                row.style.display = showByStatus && showBySearch ? '' : 'none';
            });
        }
    </script>
</body>
</html>