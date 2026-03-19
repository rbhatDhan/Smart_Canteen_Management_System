<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password</title>
<style>
body { font-family: Arial; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; background:#f5f5f5;}
.container { background: #fff; padding:30px; border-radius:10px; width:350px; box-shadow:0 0 10px rgba(0,0,0,0.2);}
input[type="email"], input[type="submit"] { width:100%; padding:12px; margin:8px 0; border-radius:5px; border:1px solid #ccc; font-size:16px;}
input[type="submit"] { background:#28a745; color:#fff; border:none; cursor:pointer;}
input[type="submit"]:hover { background:#218838; }
.error { color:red; text-align:center; }
.success { color:green; text-align:center; }
</style>
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>
    <?php
    if(isset($_GET['msg'])){
        echo '<p class="success">'.htmlspecialchars($_GET['msg']).'</p>';
    }
    if(isset($_GET['error'])){
        echo '<p class="error">'.htmlspecialchars($_GET['error']).'</p>';
    }
    ?>
    <form action="forgot_password_process.php" method="POST">
        <input type="email" name="email" placeholder="Enter your registered email" required>
        <input type="submit" value="Send Reset Link">
    </form>
    <p><a href="../student_login.php">Back to Login</a></p>
</div>
</body>
</html>
