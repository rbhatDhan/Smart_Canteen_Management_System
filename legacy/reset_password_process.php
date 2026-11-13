<?php
include '../db_connection.php';
date_default_timezone_set('Asia/Kolkata');



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['token'], $_POST['new_password'], $_POST['confirm_password'])) {
        echo "Invalid request.";
        exit;
    }

    $token = $_POST['token'];
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
        exit;
    }

    // Optional: Enforce minimum password length
    if (strlen($new_password) < 6) {
        echo "Password must be at least 6 characters.";
        exit;
    }

    // Verify token exists and not expired
    $stmt = $conn->prepare("SELECT id, reset_expires FROM users WHERE reset_token=?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo "Invalid or expired token.";
        exit;
    }

    $stmt->bind_result($user_id, $expires);
    $stmt->fetch();
    $stmt->close();

    if (strtotime($expires) < time()) {
        echo "This reset link has expired.";
        exit;
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password and clear token fields
    $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("si", $hashed_password, $user_id);

    if ($stmt->execute()) {
        echo "Password reset successfully. <a href='../student_login.php'>Login here</a>.";
    } else {
        echo "Error updating password: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
