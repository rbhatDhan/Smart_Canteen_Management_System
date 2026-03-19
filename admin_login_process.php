<?php
session_start();
include 'db_connection.php';

if(isset($_POST['username']) && isset($_POST['password'])){
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare SQL to fetch admin user by username
    $sql = "SELECT * FROM users WHERE username = ? AND role='admin' LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 1){
        $user = $result->fetch_assoc();

        // Use password_verify for hashed passwords
         // Use password_verify for hashed passwords
        // For plain text passwords only
        if ($password === $user['password']) {
    // login successful
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'admin';
            $_SESSION['user_id'] = $user['id'];

            header("Location: admin_dashboard.php");
            exit();
        } else {
            header("Location: admin_login.php?error=Invalid password");
            exit();
        }
    } else {
        header("Location: admin_login.php?error=Admin not found");
        exit();
    }
} else {
    header("Location: admin_login.php");
    exit();
}
?>