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

<!-- ✅ HTML form to enter OTP -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .otp-box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input {
            padding: 10px;
            margin: 10px 0;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .submit-btn {
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="otp-box">
    <h2>Enter OTP</h2>
    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="otp" placeholder="Enter OTP" required />
        <button class="submit-btn" type="submit" name="verify_otp">Verify OTP</button>
    </form>
</div>
</body>
</html>