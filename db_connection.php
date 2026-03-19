<?php
$servername = "localhost:3307";
$username = "root";
$password = ""; // no space inside the quotes
$database = "smartcanteen";
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//echo "Connected successfully!";
?>
