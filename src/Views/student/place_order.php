<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="assets/css/pages/place_order.css">
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
        <!-- Back Button Section -->
        <div class="back-section">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            <?php if(isset($_SESSION['username'])): ?>
                <a href="<?php echo $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'; ?>" class="dashboard-btn">
                    <i class="fas fa-tachometer-alt"></i> Back to Dashboard
                </a>
            <?php endif; ?>
            <a href="view_menu.php" class="back-btn">
                <i class="fas fa-utensils"></i> View Menu
            </a>
        </div>

        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-shopping-cart" style="color: var(--primary);"></i> Place Your Order</h1>
            <div class="day-badge">
                <i class="fas fa-calendar-day"></i> Today: <?php echo $selected_day; ?>
            </div>
        </div>

        <?php if (!empty($paid)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                Payment confirmed and order placed successfully!
            </div>
        <?php endif; ?>

        <!-- Order Card -->
        <div class="order-card">
            <?php if(!empty($dishes)): ?>
                <form id="orderForm" onsubmit="return false;">
                    <!-- Modern Menu Grid with Right-Aligned Quantity Controls -->
                    <div class="menu-grid-modern">
                        <?php foreach($dishes as $index => $dish): ?>
                            <div class="dish-card">
                                <div class="dish-icon">
                                    <i class="fas fa-<?php 
                                        echo $index % 3 == 0 ? 'hamburger' : 
                                            ($index % 3 == 1 ? 'pizza-slice' : 'mug-hot'); 
                                    ?>"></i>
                                </div>
                                <div class="dish-details">
                                    <div class="dish-info">
                                        <h3><?php echo e($dish['name']); ?></h3>
                                        <div class="price">₹<?php echo number_format($dish['price'], 2); ?></div>
                                    </div>
                                    
                                    <!-- Quantity Selector - Right Side, Original Size -->
                                    <div class="quantity-selector">
                                        <button type="button" class="qty-btn" onclick="decrementQty(<?php echo $dish['id']; ?>)">−</button>
                                        <input type="number" 
                                               id="qty-<?php echo $dish['id']; ?>"
                                               name="qty[<?php echo $dish['id']; ?>]" 
                                               class="qty-input"
                                               data-price="<?php echo $dish['price']; ?>" 
                                               min="0" max="10" value="0" 
                                               onchange="updateTotal()"
                                               readonly>
                                        <button type="button" class="qty-btn" onclick="incrementQty(<?php echo $dish['id']; ?>)">+</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Total Section -->
                    <div class="total-section-modern">
                        <span class="total-label-modern">Total Amount:</span>
                        <span class="total-amount-modern" id="totalPrice">₹0</span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button type="button" onclick="payNow()" class="btn btn-primary" id="payBtn">
                            <i class="fas fa-credit-card"></i> Pay Now
                        </button>
                        <button type="button" onclick="resetOrder()" class="btn btn-outline" id="resetBtn">
                            <i class="fas fa-redo"></i> Reset Order
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-utensil-spoon"></i>
                    <h3>No Dishes Available Today</h3>
                    <p>Please check back tomorrow for our delicious menu.</p>
                    <div style="margin-top: 2rem;">
                        <a href="view_menu.php" class="btn btn-primary">Browse Full Menu</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        window.CANTEEN_PAY = <?php echo js($payConfig); ?>;
    </script>
    <script src="assets/js/place_order.js"></script>
    <?php if (!empty($paid)): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Order Placed!',
            text: 'Your order has been placed successfully.',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
    <?php endif; ?>
</body>
</html>