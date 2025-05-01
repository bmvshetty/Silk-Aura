<?php 
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor\autoload.php'; 
include 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $shop_name = htmlspecialchars($_POST['shop_name']);
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $gst = htmlspecialchars($_POST['gst']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = $_POST['user_type'];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "❌ Passwords do not match.";
        header("Location: signup.php");
        exit();
    }

    // Store user data in session temporarily
    $_SESSION['form_data'] = [
        'name' => $name,
        'email' => $email,
        'shop_name' => $shop_name,
        'phone' => $phone,
        'address' => $address,
        'gst' => $gst,
        'password' => password_hash($password, PASSWORD_BCRYPT),
        'user_type' => $user_type
    ];

    // Generate OTP
    $otp = rand(100000, 999999);
    $_SESSION['otp'] = $otp;
    $_SESSION['email'] = $email;

    // Send OTP
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'vbm5274@gmail.com';
        $mail->Password = 'mpngsriasrltmsoz';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('vbm5274@gmail.com', 'Silk Aura');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Sign-Up';
        $mail->Body = "<h3>Your OTP is: <strong>$otp</strong></h3>";

        $mail->send();
        $_SESSION['otp_message'] = "✅ OTP sent to $email.";
        header("Location: verify_otp.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['otp_message'] = "❌ OTP send failed: " . $mail->ErrorInfo;
        header("Location: signup.php");
        exit();
    }
}
?>
