<?php
ob_start();  // ✅ Start output buffering
session_start();
include 'db_config.php';  // MySQLi connection
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ✅ Check classic CAPTCHA
    if (!isset($_POST['captcha']) || $_POST['captcha'] !== $_SESSION['captcha']) {
        $error = "Invalid CAPTCHA. Please try again.";
    } else {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];

    // ✅ 1. TRACK LOGIN ATTEMPTS (Last 15 minutes)
    $stmt = $conn->prepare("SELECT COUNT(*) AS attempts FROM login_attempts WHERE email = ? AND attempt_time > (NOW() - INTERVAL 15 MINUTE)");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $attempts = $result->fetch_assoc()['attempts'];
    $stmt->close();

    // ✅ Block user after 5 failed attempts
    if ($attempts >= 5) {
        $error = "Too many failed attempts. Please try again later.";
    } else {
        // ✅ 2. CHECK USER LOGIN
        $query = "SELECT * FROM users WHERE name = ? AND email = ? AND user_type = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $name, $email, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // ✅ 3. VERIFY PASSWORD
            if (password_verify($password, $user['password'])) {
                // Successful login: Reset attempts
                $stmt = $conn->prepare("DELETE FROM login_attempts WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->close();

                // ✅ Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['name'] = $user['name'];

                // ✅ Redirect based on user type
                if ($user['user_type'] == 'Buyer') {
                    header("Location:buyers.php");
                    exit();
                } else {
                    header("Location: sellers.php");
                    exit();
                }
            } else {
                // ✅ 4. INSERT FAILED ATTEMPT
                $stmt = $conn->prepare("INSERT INTO login_attempts (email) VALUES (?)");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->close();

                $error = "Invalid password.";
            }
        } else {
            $error = "Invalid name, email, or user type.";
        }
    }
}
    $conn->close();
}

ob_end_flush();  // ✅ End output buffering
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background: url("http://localhost/img/raw-silk-thread-14765374.webp") no-repeat center center/cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .neumorphic-card {
            background: #e0e5ec;
            border-radius: 20px;
            box-shadow: 10px 10px 20px #a3b1c6, -10px -10px 20px #ffffff;
            width: 450px;
            padding: 40px;
            text-align: center;
        }
        h1 { color: #444; margin-bottom: 20px; }
        .error { color: red; }
        .neumorphic-input, .neumorphic-button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 10px;
            border: none;
            box-shadow: inset 5px 5px 10px #a3b1c6, inset -5px -5px 10px #ffffff;
        }
        .neumorphic-button {
            background: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .neumorphic-button:hover {
            background: #45a049;
        }
        a {
            display: block;
            margin: 15px 0;
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<div class="neumorphic-card">
    <h1>Login</h1>
    
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" autocomplete="on">
        <input type="text" name="name" class="neumorphic-input" placeholder="Full Name" required autocomplete="name"/>
        <input type="email" name="email" class="neumorphic-input" placeholder="Email" required autocomplete="email"/>
        <input type="password" name="password" class="neumorphic-input" placeholder="Password" required autocomplete="current-password"/>

        <div>
            <label><input type="radio" name="user_type" value="buyer" required> Buyer</label>
            <label><input type="radio" name="user_type" value="seller" required> Seller</label>
        </div>

        <div style="margin: 10px 0;">
            <img src="captcha.php" alt="CAPTCHA Image"><br>
            <input type="text" name="captcha" class="neumorphic-input" placeholder="Enter CAPTCHA" required>
        </div>


        <button type="submit" class="neumorphic-button">Login</button>

        <!-- ✅ Forgot Password Link -->
        <a href="forgot_password.php">Forgot Password?</a>
    </form>
</div>
</body>
</html>
