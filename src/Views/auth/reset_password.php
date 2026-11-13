<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password</title>
<style>
body { font-family: Arial; display:flex; justify-content:center; align-items:center; height:100vh; background:#f5f5f5; margin:0;}
.container { background:#fff; padding:30px; border-radius:10px; width:350px; box-shadow:0 0 10px rgba(0,0,0,0.2);}
input[type="password"], input[type="submit"] { width:100%; padding:12px; margin:8px 0; border-radius:5px; border:1px solid #ccc; font-size:16px;}
input[type="submit"] { background:#28a745; color:#fff; border:none; cursor:pointer;}
input[type="submit"]:hover { background:#218838; }
.error { color:red; text-align:center; }
.success { color:green; text-align:center; }
</style>
</head>
<body>
<div class="container">
<h2>Reset Password</h2>
<?php if($error){ echo "<p class='error'>" . e($error) . "</p>"; } ?>
<form method="POST">
    <?php echo \SmartCanteen\Core\Csrf::field(); ?>
    <input type="password" name="new_password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <input type="submit" value="Reset Password">
</form>
</div>
</body>
</html>
