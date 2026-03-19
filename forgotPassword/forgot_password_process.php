<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

include '../db_connection.php';

// Set timezone
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Trim email input (do not lowercase)
    $email = trim($_POST['email']);

    // Step 1: Check if email exists (case-insensitive & trim)
    $stmt = $conn->prepare("SELECT id FROM users WHERE TRIM(LOWER(email)) = LOWER(?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $stmt->close();
        header("Location: forgot_password.php?error=Email not found");
        exit;
    }
    $stmt->close();

    // Step 2: Generate token & expiry
    $token = bin2hex(random_bytes(16)); // 32-character token
    $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

    // Step 3: Save token & expiry in DB (case-insensitive email match)
    $stmt = $conn->prepare("
        UPDATE users 
        SET reset_token=?, reset_expires=? 
        WHERE TRIM(LOWER(email)) = LOWER(?)
    ");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $token, $expires, $email);

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    if ($stmt->affected_rows == 0) {
        die("Warning: Token not updated. Email may not match exactly in DB.");
    }
    $stmt->close();

    // Step 4: Send reset email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dblearnnewstuff@gmail.com';
        $mail->Password = 'ugpsgnlxlckfscdi'; // App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('dblearnnewstuff@gmail.com', 'Canteen App');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Link';
        $reset_link = "http://localhost/CanteenProject/forgotPassword/reset_password.php?token=" . $token;
        $mail->Body = "Click this link to reset your password:<br><a href='$reset_link'>$reset_link</a>";

        $mail->send();
        header("Location: forgot_password.php?msg=Reset link sent to your email!");
        exit;

    } catch (Exception $e) {
        header("Location: forgot_password.php?error=Mailer Error: " . $mail->ErrorInfo);
        exit;
    }
}
?>
