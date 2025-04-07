<?php
$host = "localhost";
$db_name = "silk_aura";
$username = "root";
$password = "";

// Create connection using MySQLi
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8 (optional but recommended)
$conn->set_charset("utf8");

?>
