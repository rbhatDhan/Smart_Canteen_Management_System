<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="modern-style.css">
    <style>
        .home-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .welcome-card {
            background: white;
            border-radius: 2rem;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow-lg);
            animation: fadeIn 0.6s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .welcome-icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        .welcome-card h2 {
            font-size: 2rem;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        .welcome-card p {
            color: var(--gray);
            margin-bottom: 2rem;
            line-height: 1.7;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
        }
        .feature {
            text-align: center;
        }
        .feature i {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        .feature h3 {
            font-size: 1rem;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }
        .feature p {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 0;
        }
        .quick-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        .action-btn {
            padding: 0.75rem 1.5rem;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .action-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        @media (max-width: 640px) {
            .welcome-card {
                padding: 2rem;
            }
            .welcome-card h2 {
                font-size: 1.5rem;
            }
            .features {
                grid-template-columns: 1fr;
            }
            .quick-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="home-container">
        <div class="welcome-card">
            <div class="welcome-icon">
                <i class="fas fa-utensils"></i>
            </div>
            <h2>Welcome to Smart Canteen</h2>
            <p>Manage your meals easily! Browse the menu, place orders, and track your order history all in one place.</p>
            
            <div class="features">
                <div class="feature">
                    <i class="fas fa-eye"></i>
                    <h3>View Menu</h3>
                    <p>Browse daily specials</p>
                </div>
                <div class="feature">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Place Orders</h3>
                    <p>Order instantly</p>
                </div>
                <div class="feature">
                    <i class="fas fa-history"></i>
                    <h3>Track History</h3>
                    <p>View past orders</p>
                </div>
            </div>
            
            <div class="quick-actions">
                <a href="view_menu.php" class="action-btn">
                    <i class="fas fa-utensils"></i> Browse Menu
                </a>
                <a href="place_order.php" class="action-btn">
                    <i class="fas fa-shopping-cart"></i> Order Now
                </a>
            </div>
        </div>
    </div>
</body>
</html>