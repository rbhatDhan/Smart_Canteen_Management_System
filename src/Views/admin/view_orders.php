<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="assets/css/pages/view_orders.css">
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

            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon total"><i class="fas fa-shopping-bag"></i></div>
                    <div class="stat-info">
                        <h3>Total Orders</h3>
                        <div class="number"><?php echo (int) $counts['total']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <h3>Pending</h3>
                        <div class="number"><?php echo (int) $counts['pending']; ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon completed"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-info">
                        <h3>Completed</h3>
                        <div class="number"><?php echo (int) $counts['completed']; ?></div>
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
                            <?php if ($rows): foreach ($rows as $row):
                                $payment = (string) $row['payment_status'];
                                $status  = (string) $row['status'];
                                $disabled = '';
                                if ($payment === 'Unsuccessful') {
                                    $status = 'Pending';
                                    $disabled = 'disabled';
                                }
                                $search = strtolower($row['number'] . ' ' . $row['usn'] . ' ' . $row['label']);
                            ?>
                                <tr data-status="<?php echo e($status); ?>" data-search="<?php echo e($search); ?>">
                                    <td><strong>#<?php echo e($row['number']); ?></strong></td>
                                    <td><?php echo e($row['usn']); ?></td>
                                    <td><?php echo e($row['label']); ?></td>
                                    <td><?php echo (int) $row['quantity']; ?></td>
                                    <td><?php echo e(date('d M Y, h:i A', strtotime($row['order_time']))); ?></td>
                                    <td><span class="payment-badge <?php echo e($payment); ?>"<?php echo !empty($row['payment_ref']) ? ' title="' . e($row['payment_ref']) . '"' : ''; ?>><?php echo e($payment); ?></span></td>
                                    <td>
                                        <select onchange="updateStatus(<?php echo (int) $row['key']; ?>, this, <?php echo e(js($row['number'])); ?>)"
                                                class="status-select <?php echo e($status); ?>"
                                                <?php echo $disabled; ?>>
                                            <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                            <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>✅ Completed</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="7" style="text-align: center; padding: 3rem; color: var(--gray);">No orders found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const CSRF_TOKEN = <?php echo js($csrf); ?>;

        function updateStatus(orderKey, selectElement, orderNumber) {
            const newStatus = selectElement.value;
            const previous = newStatus === 'Pending' ? 'Completed' : 'Pending';

            Swal.fire({
                title: 'Update Status',
                text: `Change order #${orderNumber} status to ${newStatus}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d9488',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, update'
            }).then((result) => {
                if (!result.isConfirmed) {
                    selectElement.value = previous;
                    return;
                }
                fetch('api/orders_update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-CSRF-Token': CSRF_TOKEN
                    },
                    body: new URLSearchParams({ id: orderKey, status: newStatus })
                })
                .then(response => response.json().catch(() => ({ success: false })).then(data => ({ ok: response.ok, data })))
                .then(({ ok, data }) => {
                    if (!ok || !data.success) {
                        throw new Error(data.message || 'Failed to update status');
                    }
                    selectElement.className = 'status-select ' + newStatus;
                    selectElement.closest('tr').setAttribute('data-status', newStatus);
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: `Order #${orderNumber} is now ${newStatus}`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                })
                .catch(err => {
                    selectElement.value = previous;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message || 'Failed to update status'
                    });
                });
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