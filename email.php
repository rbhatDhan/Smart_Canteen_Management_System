<?php
// update_status.php
include 'db_connection.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = intval($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');

    if ($orderId <= 0 || empty($status)) {
        http_response_code(400);
        echo "Invalid data.";
        exit;
    }

    // --- 1️⃣ Update status in the database ---
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderId);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo "Database update failed: " . $stmt->error;
        exit;
    }
    $stmt->close();

    // --- 2️⃣ If status is 'Completed', send notification email ---
    if ($status === 'Completed') {
        $sql = "SELECT u.email, u.username, m.name AS dish_name
                FROM orders o
                JOIN users u ON o.student_id = u.id
                JOIN menu m ON o.dish_id = m.id
                WHERE o.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $email = $row['email'];
            $studentName = $row['username'];
            $dishName = $row['dish_name'];

            // --- 3️⃣ Send mail using PHPMailer ---
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'dblearnnewstuff@gmail.com'; // your sender email
                $mail->Password = 'ugpsgnlxlckfscdi'; // your app password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('dblearnnewstuff@gmail.com', 'Smart Canteen');
                $mail->addAddress($email, $studentName);

                $mail->isHTML(true);
                $mail->Subject = "Order #$orderId Completed ✅";
                $mail->Body = "
                    <p>Hi <b>$studentName</b>,</p>
                    <p>Your order for <b>$dishName</b> (Order ID: <b>$orderId</b>) has been marked as <b>Completed</b>.</p>
                    <p>Thank you for using Smart Canteen!</p>
                    <hr>
                    <small>This is an automated notification from the Smart Canteen system.</small>
                ";

                $mail->send();
                echo "Order status updated and email notification sent.";
            } catch (Exception $e) {
                echo "Order updated, but email failed: {$mail->ErrorInfo}";
            }
        } else {
            echo "Order updated, but student details not found.";
        }
        $stmt->close();
    } else {
        echo "Order status updated successfully (no mail needed).";
    }
}
?>
