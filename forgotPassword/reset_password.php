<?php
session_start();
include '../db_connection.php';
date_default_timezone_set('Asia/Kolkata');

// Step 0: Validate token from URL
if (!isset($_GET['token'])) {
    die("Invalid request");
}

$token = trim($_GET['token']); // Trim any accidental spaces

// Step 1: Verify token and expiry
$stmt = $conn->prepare("
    SELECT id FROM users 
    WHERE TRIM(reset_token) = ? AND reset_expires > NOW()
");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    die("Invalid or expired token");
}

$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Step 2: Validate passwords
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Step 3: Hash password
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        // Step 4: Update password and clear token/expiry
        $stmt = $conn->prepare("
            UPDATE users 
            SET password=?, reset_token=NULL, reset_expires=NULL 
            WHERE id=? AND TRIM(reset_token)=?
        ");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sis", $hashed_password, $user_id, $token);

        if ($stmt->execute()) {
            if ($stmt->affected_rows == 0) {
                $error = "Failed to reset password. Token may be invalid or expired.";
            } else {
                $stmt->close();
                header("Location: ../student_login.php?msg=Password reset successfully!");
                exit;
            }
        } else {
            $error = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
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
<?php if($error){ echo "<p class='error'>$error</p>"; } ?>
<form method="POST">
    <input type="password" name="new_password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <input type="submit" value="Reset Password">
</form>
</div>
</body>
</html>
