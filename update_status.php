<?php
// Enable error logging but don't display errors
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to cleanly handle JSON responses
ob_start();

header('Content-Type: application/json');

include 'db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'email_sent' => false
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Validate input
if (!isset($_POST['id']) || !isset($_POST['status'])) {
    $response['message'] = 'Missing required fields';
    echo json_encode($response);
    exit;
}

$id = intval($_POST['id']);
$status = trim($_POST['status']);

if ($id <= 0 || !in_array($status, ['Pending', 'Completed'])) {
    $response['message'] = 'Invalid order ID or status';
    echo json_encode($response);
    exit;
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Update order status in the database
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Database prepare error: " . $conn->error);
    }
    
    $stmt->bind_param("si", $status, $id);
    
    if (!$stmt->execute()) {
        throw new Exception("Database update failed: " . $stmt->error);
    }
    
    // Check if any row was affected
    if ($stmt->affected_rows === 0) {
        throw new Exception("Order not found or status unchanged");
    }
    
    $stmt->close();
    
    $response['success'] = true;
    $response['message'] = "Order #{$id} status updated to {$status}";
    
    // If status is 'Completed', send notification email
    if ($status === 'Completed') {
        // Fetch user info using USN
        $user_query = $conn->prepare("
            SELECT u.username, u.email, u.usn, o.id AS order_id, 
                   (SELECT GROUP_CONCAT(m.name SEPARATOR ', ') 
                    FROM orders o2 
                    JOIN menu m ON o2.dish_id = m.id 
                    WHERE o2.id = o.id) as items
            FROM orders o
            INNER JOIN users u ON o.usn = u.usn
            WHERE o.id = ?
        ");
        
        if (!$user_query) {
            throw new Exception("Failed to prepare user query");
        }
        
        $user_query->bind_param("i", $id);
        $user_query->execute();
        $user_result = $user_query->get_result();
        
        if ($user_result && $user = $user_result->fetch_assoc()) {
            $username = $user['username'];
            $email = $user['email'];
            $usn = $user['usn'];
            $order_id = $user['order_id'];
            $items = $user['items'] ?? 'your order';
            
            $user_query->close();

            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                // Send email using PHPMailer
                $mail = new PHPMailer(true);
                
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'varshinigbhat@gmail.com'; // Your Gmail
                    $mail->Password = 'wiag iyct dftf yadg';     // Gmail app password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->Timeout = 10; // Timeout in seconds
                    
                    // Recipients
                    $mail->setFrom('varshinigbhat@gmail.com', 'Smart Canteen');
                    $mail->addAddress($email, $username);
                    $mail->addReplyTo('varshinigbhat@gmail.com', 'Smart Canteen Support');
                      
                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = "Order #{$order_id} is Ready for Pickup!";
                    
                    // HTML email body with modern styling
                    $mail->Body = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <style>
                            body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                            .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
                            .badge { background: #0d9488; color: white; padding: 5px 15px; border-radius: 20px; display: inline-block; font-size: 14px; }
                            .order-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0d9488; }
                            .footer { text-align: center; margin-top: 30px; color: #64748b; font-size: 14px; }
                            .btn { display: inline-block; padding: 12px 24px; background: #0d9488; color: white; text-decoration: none; border-radius: 8px; margin-top: 20px; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1 style='margin:0;'>🍽️ Smart Canteen</h1>
                            </div>
                            <div class='content'>
                                <h2>Hello {$username}! 👋</h2>
                                <p>Great news! Your order is now ready for pickup.</p>
                                
                                <div class='order-details'>
                                    <p><strong>Order ID:</strong> #{$order_id}</p>
                                    <p><strong>Items:</strong> {$items}</p>
                                    <p><strong>Status:</strong> <span class='badge'>✅ Completed</span></p>
                                    <p><strong>USN:</strong> {$usn}</p>
                                </div>
                                
                                <p>Please visit the canteen counter to collect your order.</p>
                                <p>Thank you for using Smart Canteen!</p>
                                
                                <div style='text-align: center;'>
                                    <a href='http://localhost/smartcanteen/order_history.php' class='btn'>View Order History</a>
                                </div>
                            </div>
                            <div class='footer'>
                                <p>This is an automated message from Smart Canteen Management System.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    // Plain text alternative
                    $mail->AltBody = "Hello {$username},\n\nYour order (#{$order_id}) is now ready for pickup at the canteen.\n\nItems: {$items}\n\nThank you for using Smart Canteen!";
                    
                    $mail->send();
                    $response['email_sent'] = true;
                    $response['message'] .= " and email notification sent to {$email}";
                    
                } catch (Exception $e) {
                    // Log email error but don't fail the whole request
                    error_log("Mailer Error for order #{$id}: " . $mail->ErrorInfo);
                    $response['email_sent'] = false;
                    $response['message'] .= " (email notification failed, but order status updated)";
                }
            } else {
                $response['message'] .= " (no valid email found for user)";
            }
        } else {
            $response['message'] .= " (user details not found for email)";
        }
    }
    
    // Commit transaction
    $conn->commit();
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("Order status update error: " . $e->getMessage());
}

// Close connection
if (isset($conn)) {
    $conn->close();
}

// Clear output buffer and send JSON response
ob_end_clean();
echo json_encode($response);
exit;
?>