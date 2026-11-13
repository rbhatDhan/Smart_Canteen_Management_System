<?php
session_start();

// Simulate student login (for demo)
if(!isset($_SESSION['student_name'])){
    $_SESSION['student_name'] = "Dhanya";
    $_SESSION['student_id'] = 1;
}

include '../db_connection.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Initialize cart
if(!isset($_SESSION['cart'])){ $_SESSION['cart'] = []; }

// Add item to cart
if(isset($_POST['item_id'])){
    $id = $_POST['item_id'];
    $qty = $_POST['quantity'];
    if(isset($_SESSION['cart'][$id])){
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
}

// QR code library
include 'phpqrcode/qrlib.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard</title>
<style>
body{font-family:Arial; background:#f5f5f5; margin:0; padding:0;}
header{background:#333; color:#fff; padding:10px 20px; display:flex; justify-content:space-between; align-items:center;}
header nav a{color:#fff; margin-left:20px; text-decoration:none;}
.menu, .cart{padding:20px;}
.menu-items{display:flex; flex-wrap:wrap; gap:20px;}
.menu-item{background:#fff; padding:10px; border-radius:8px; width:200px; text-align:center; box-shadow:0 0 5px rgba(0,0,0,0.2);}
.menu-item img{width:100%; height:150px; object-fit:cover;}
.cart{background:#fff; margin:20px; border-radius:8px;}
button{padding:5px 10px; margin-top:5px;}
</style>
</head>
<body>

<header>
<h1>Welcome, <?php echo $_SESSION['student_name']; ?></h1>
<nav>
<a href="dashboard.php">Home</a>
<a href="dashboard.php#cart">Cart</a>
<a href="dashboard.php?clear=1">Logout</a>
</nav>
</header>

<main>
<section class="menu">
<h2>Menu</h2>
<div class="menu-items">
<?php
$menu = $conn->query("SELECT * FROM menu");
while($row = $menu->fetch_assoc()){
    echo '
    <div class="menu-item">
        <img src="images/'.$row['image'].'" alt="'.$row['name'].'">
        <h3>'.$row['name'].'</h3>
        <p>Price: ₹'.$row['price'].'</p>
        <form method="POST">
            <input type="hidden" name="item_id" value="'.$row['id'].'">
            <input type="number" name="quantity" value="1" min="1">
            <button type="submit">Add to Cart</button>
        </form>
    </div>';
}
?>
</div>
</section>

<section class="cart" id="cart">
<h2>Your Cart</h2>
<?php
$total = 0;
if(!empty($_SESSION['cart'])){
    foreach($_SESSION['cart'] as $id => $qty){
        $item = $conn->query("SELECT * FROM menu WHERE id=$id")->fetch_assoc();
        $subtotal = $item['price'] * $qty;
        $total += $subtotal;
        echo '<p>'.$item['name'].' x '.$qty.' = ₹'.$subtotal.'</p>';
    }
    echo '<h3>Total: ₹'.$total.'</h3>';

    // Generate QR code for payment
    $order_data = "student_id=".$_SESSION['student_id']."&total=".$total."&time=".time();
    $filename = "qrcodes/order".$_SESSION['student_id'].".png";
    if(!file_exists("qrcodes")){ mkdir("qrcodes"); }
    QRcode::png($order_data, $filename, "L", 4, 2);
    echo '<p>Scan to Pay:</p><img src="'.$filename.'" alt="QR Code">';
} else {
    echo "<p>Cart is empty.</p>";
}

// Clear session/cart on logout
if(isset($_GET['clear'])){
    session_destroy();
    header("Location: dashboard.php");
    exit;
}
?>
</section>
</main>

</body>
</html>
