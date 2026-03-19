<?php
session_start();
include 'db_connection.php';

if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header("Location: student_login.php?error=Please fill all fields");
    exit();
}

$user_input = trim($_POST['username']);
$pass = trim($_POST['password']);

// Only fetch users with role='student'
$stmt = $conn->prepare("SELECT id, username, usn, password FROM users WHERE (username = ? OR usn = ?) AND role='student'");
$stmt->bind_param("ss", $user_input, $user_input);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    if (password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['usn'] = $user['usn'];
        $_SESSION['role'] = 'student';

        header("Location: student_dashboard.php");
        exit();
    } else {
        header("Location: student_login.php?error=Invalid username/USN or password");
        exit();
    }
} else {
    header("Location: student_login.php?error=Invalid username/USN or password");
    exit();
}

$stmt->close();
$conn->close();
?>