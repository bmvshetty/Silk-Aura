<?php
session_start();
include 'db_config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    $otp_entered = $_POST['otp'];

    if ($otp_entered == $_SESSION['otp']) {
        $form_data = $_SESSION['form_data'];

        // Insert user data into the database using MySQLi with question mark placeholders
        $stmt = $conn->prepare("INSERT INTO users (name, email, shop_name, phone, address, gst, password, user_type) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "ssssssss",
            $form_data['name'],
            $form_data['email'],
            $form_data['shop_name'],
            $form_data['phone'],
            $form_data['address'],
            $form_data['gst'],
            $form_data['password'],
            $form_data['user_type']
        );

        if ($stmt->execute()) {
            $message = "✅ User registered successfully!";
            session_unset();  // Clear session data
            header("Location: login.php");
            exit();
        } else {
            $message = "❌ Registration failed: " . $stmt->error;
        }
    } else {
        $message = "❌ Invalid OTP. Please try again.";
    }
}
?>
