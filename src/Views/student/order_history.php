<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/modern-style.css">
<link rel="stylesheet" href="assets/css/pages/order_history.css">
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
                    <div class="user-greeting">Welcome back, <?php echo e($username); ?>!</div>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                        <div class="stat-value"><?php echo $totalOrders; ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-star"></i></div>
                        <div class="stat-value"><?php echo e($favoriteDish); ?></div>
                        <div class="stat-label">Favorite Dish</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                        <div class="stat-value"><?php echo e($memberSince); ?></div>
                        <div class="stat-label">Member Since</div>
                    </div>
                </div>

                <?php if($orders): ?>
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
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $row): ?>
                                    <?php $given = (int) ($ratings[$row['key']] ?? 0); ?>
                                    <tr>
                                        <td class="order-id">#<?php echo e($row['number']); ?></td>
                                        <td><?php echo e($row['label']); ?></td>
                                        <td><?php echo (int) $row['quantity']; ?></td>
                                        <td><?php echo e(date('d M Y, h:i A', strtotime($row['order_time']))); ?></td>
                                        <td><span class="status-badge <?php echo e(strtolower($row['status'])); ?>"><?php echo e($row['status']); ?></span></td>
                                        <td><span class="payment-badge"><?php echo e($row['payment_status']); ?></span></td>
                                        <td>
                                            <?php if ($row['status'] === 'Completed'): ?>
                                                <span class="rating-stars" data-order="<?php echo (int) $row['key']; ?>" data-rating="<?php echo $given; ?>" role="radiogroup" aria-label="Rate order <?php echo e($row['number']); ?>">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <button type="button" class="rating-star<?php echo $i <= $given ? ' filled' : ''; ?>" data-value="<?php echo $i; ?>" role="radio" aria-checked="<?php echo $i === $given ? 'true' : 'false'; ?>" aria-label="<?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?>">★</button>
                                                    <?php endfor; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="rating-na">After it is completed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>window.CANTEEN_RATE = <?php echo js(['csrf' => $csrf, 'url' => 'api/ratings_submit.php']); ?>;</script>
    <script src="assets/js/ratings.js"></script>
</body>
</html>