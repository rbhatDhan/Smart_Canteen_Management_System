<?php
session_start();
include 'db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: student_login.php?error=Please login first");
    exit();
}

$student_id = $_SESSION['user_id'];
$selected_day = date('l');
$dishes = [];
$message = "";

$stmt = $conn->prepare("SELECT id, name, price FROM menu WHERE menu_day = ?");
$stmt->bind_param("s", $selected_day);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $dishes[] = $row;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $order_time = date('Y-m-d H:i:s');
    $payment_status = 'Successful';
    $status = 'Pending';
    $hasOrder = false;

    foreach ($_POST['qty'] as $dish_id => $qty) {
        if ((int)$qty > 0) {
            $usn = $_SESSION['usn'];

            $stmt = $conn->prepare("INSERT INTO orders (student_id, dish_id, quantity, order_time, status, payment_status, usn)
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iiissss", $student_id, $dish_id, $qty, $order_time, $status, $payment_status, $usn);
            $stmt->execute();
            $hasOrder = true;
            $stmt->close();
        }
    }

    if ($hasOrder) {
        $message = "success|Payment confirmed and order placed successfully!";

        $user_query = $conn->prepare("SELECT username, email FROM users WHERE usn = ?");
        $user_query->bind_param("s", $usn);
        $user_query->execute();
        $user_query->bind_result($user_name, $user_email);
        $user_query->fetch();
        $user_query->close();

        if ($payment_status == 'Successful') {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'varshinigbhat@gmail.com';
                $mail->Password = 'wiag iyct dftf yadg';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('varshinigbhat@gmail.com', 'Smart Canteen');
                $mail->addAddress($user_email, $user_name);

                $mail->isHTML(true);
                $mail->Subject = 'Order Confirmation';
                $mail->Body = "
                    <h2>Order Confirmation</h2>
                    <p>Hi $user_name,</p>
                    <p>Your order has been placed successfully!</p>
                    <p><strong>Payment Status:</strong> $payment_status</p>
                    <p><strong>Order Status:</strong> $status</p>
                    <p>You will be notified once the order is ready.</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                error_log("Mailer Error: " . $mail->ErrorInfo);
            }
        }
    } else {
        $message = "error|Please select at least one dish before confirming payment.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - Smart Canteen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
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
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f8fafc;
            min-height: 100vh;
            padding: 2rem;
            position: relative;
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* Back Button Section */
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
            border: 1px solid var(--primary-soft);
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-5px);
            box-shadow: 0 10px 20px rgba(13,148,136,0.2);
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

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 2.8rem;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .header .day-badge {
            display: inline-block;
            padding: 0.6rem 2rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 0.5rem;
            box-shadow: 0 10px 20px rgba(13,148,136,0.2);
        }

        /* Order Card */
        .order-card {
            background: white;
            border-radius: 40px;
            padding: 2.5rem;
            box-shadow: 0 20px 30px -10px rgba(0,0,0,0.1);
            border: 1px solid rgba(13,148,136,0.1);
        }

        /* Modern Menu Grid */
        .menu-grid-modern {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dish-card {
            background: white;
            border-radius: 25px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s;
            border: 1px solid var(--primary-soft);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .dish-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-soft), transparent);
            opacity: 0;
            transition: opacity 0.3s;
            z-index: 0;
        }

        .dish-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 20px 30px rgba(13,148,136,0.15);
        }

        .dish-card:hover::before {
            opacity: 0.3;
        }

        .dish-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-soft), white);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary);
            border: 2px solid var(--primary-soft);
            z-index: 1;
            transition: all 0.3s;
            flex-shrink: 0;
        }

        .dish-card:hover .dish-icon {
            background: var(--primary);
            color: white;
            transform: rotate(10deg);
        }

        .dish-details {
            flex: 1;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .dish-info h3 {
            font-size: 1.2rem;
            color: var(--dark);
            margin-bottom: 0.3rem;
            font-weight: 600;
        }

        .dish-info .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        /* Quantity Selector - Original Size, Positioned Right */
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            z-index: 1;
            margin-left: 1rem;
        }

        .qty-btn {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            border: none;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-btn:hover {
            background: var(--primary);
            color: white;
        }

        .qty-input {
            width: 50px;
            height: 35px;
            border: 2px solid var(--primary-soft);
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            font-size: 1rem;
        }

        .qty-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        /* Total Section */
        .total-section-modern {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 1rem;
            border-radius: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 2rem 0;
            color: white;
            box-shadow: 0 20px 30px rgba(13,148,136,0.3);
        }

        .total-label-modern {
            font-size: 1.3rem;
            font-weight: 500;
            opacity: 0.9;
        }

        .total-amount-modern {
            font-size: 3rem;
            font-weight: 700;
        }

        /* Payment Section */
        .payment-section-modern {
            text-align: center;
            margin: 2rem 0;
            padding: 2.5rem;
            background: white;
            border-radius: 30px;
            display: none;
            box-shadow: 0 20px 30px rgba(0,0,0,0.05);
            border: 2px dashed var(--primary-soft);
        }

        .qr-container-modern {
            margin: 2rem auto;
            padding: 1.5rem;
            background: white;
            display: inline-block;
            border-radius: 30px;
            box-shadow: 0 20px 30px rgba(13,148,136,0.15);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 1.1rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(13,148,136,0.2);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13,148,136,0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-3px);
        }

        .empty-state {
            text-align: center;
            padding: 4rem;
        }

        .empty-state i {
            font-size: 5rem;
            color: var(--primary-soft);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--gray);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        @media (max-width: 768px) {
            .order-card {
                padding: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .dish-card {
                flex-direction: column;
                text-align: center;
            }
            
            .dish-details {
                flex-direction: column;
                gap: 1rem;
                width: 100%;
            }
            
            .quantity-selector {
                margin-left: 0;
                justify-content: center;
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

        <?php if(!empty($message)): 
            list($type, $text) = explode('|', $message);
        ?>
            <div class="alert alert-<?php echo $type === 'success' ? 'success' : 'error'; ?>">
                <i class="fas fa-<?php echo $type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $text; ?>
            </div>
        <?php endif; ?>

        <!-- Order Card -->
        <div class="order-card">
            <?php if(!empty($dishes)): ?>
                <form method="POST" id="orderForm">
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
                                        <h3><?php echo htmlspecialchars($dish['name']); ?></h3>
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

                    <!-- Payment Section -->
                    <div class="payment-section-modern" id="paymentSection">
                        <h3 style="margin-bottom: 1rem; color: var(--dark);">Scan to Pay with UPI</h3>
                        <div id="qrContainer" class="qr-container-modern"></div>
                        <p style="color: var(--gray); margin-top: 1rem;">
                            <i class="fas fa-info-circle"></i> Scan this QR code with any UPI app
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button type="button" onclick="generateQR()" class="btn btn-primary" id="generateBtn">
                            <i class="fas fa-qrcode"></i> Generate QR Code
                        </button>
                        <button type="submit" name="confirm_payment" class="btn btn-success" id="confirmBtn" style="display: none;">
                            <i class="fas fa-check-circle"></i> Confirm Payment
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
        let totalAmount = 0;

        function incrementQty(dishId) {
            const input = document.getElementById('qty-' + dishId);
            let value = parseInt(input.value) || 0;
            if (value < 10) {
                input.value = value + 1;
                updateTotal();
            }
        }

        function decrementQty(dishId) {
            const input = document.getElementById('qty-' + dishId);
            let value = parseInt(input.value) || 0;
            if (value > 0) {
                input.value = value - 1;
                updateTotal();
            }
        }

        function updateTotal() {
            const inputs = document.querySelectorAll('.qty-input');
            let total = 0;
            
            inputs.forEach(input => {
                const price = parseFloat(input.getAttribute('data-price'));
                const qty = parseInt(input.value) || 0;
                total += price * qty;
            });
            
            totalAmount = total;
            document.getElementById('totalPrice').innerHTML = '₹' + total.toFixed(0);
        }

        function generateQR() {
            if (totalAmount <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Selected',
                    text: 'Please select at least one dish!',
                    confirmButtonColor: '#0d9488'
                });
                return;
            }

            const upiId = "9945499642@axl";
            const upiUrl = `upi://pay?pa=${upiId}&pn=Canteen&am=${totalAmount}&cu=INR`;

            const qrContainer = document.getElementById('qrContainer');
            qrContainer.innerHTML = '';
            
            new QRCode(qrContainer, {
                text: upiUrl,
                width: 200,
                height: 200,
                colorDark: "#0d9488",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });

            document.getElementById('paymentSection').style.display = 'block';
            document.getElementById('confirmBtn').style.display = 'inline-flex';
            document.getElementById('generateBtn').style.display = 'none';

            document.getElementById('paymentSection').scrollIntoView({ behavior: 'smooth' });
        }

        function resetOrder() {
            document.querySelectorAll('.qty-input').forEach(input => {
                input.value = 0;
            });
            updateTotal();
            
            document.getElementById('paymentSection').style.display = 'none';
            document.getElementById('confirmBtn').style.display = 'none';
            document.getElementById('generateBtn').style.display = 'inline-flex';
            
            Swal.fire({
                icon: 'info',
                title: 'Order Reset',
                text: 'Your order has been reset.',
                timer: 2000,
                showConfirmButton: false
            });
        }

        document.getElementById('orderForm').addEventListener('submit', function(e) {
            if (totalAmount <= 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Empty Order',
                    text: 'Please select items before confirming payment!'
                });
            }
        });

        <?php if(isset($message) && strpos($message, 'success') !== false): ?>
        Swal.fire({
            icon: 'success',
            title: 'Order Placed!',
            text: 'Your order has been placed successfully.',
            timer: 3000,
            showConfirmButton: false
        });
        <?php endif; ?>
    </script>
</body>
</html>