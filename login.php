<?php
ob_start();
session_start();
include 'db_config.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CAPTCHA Check
    if (!isset($_POST['captcha']) || $_POST['captcha'] !== $_SESSION['captcha']) {
        $error = "Invalid CAPTCHA. Please try again.";
    } else {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $user_type = $_POST['user_type'];

        // Track login attempts (last 15 minutes)
        $stmt = $conn->prepare("SELECT COUNT(*) AS attempts FROM login_attempts WHERE email = ? AND attempt_time > (NOW() - INTERVAL 15 MINUTE)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $attempts = $result->fetch_assoc()['attempts'];
        $stmt->close();

        if ($attempts >= 5) {
            $error = "Too many failed attempts. Please try again later.";
        } else {
            // Check user login
            $query = "SELECT * FROM users WHERE name = ? AND email = ? AND user_type = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $name, $email, $user_type);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                // Check if user is marked deleted
                if ($user['status'] === 'deleted') {
                    echo "<script>alert('Your account has been deleted by the admin.'); window.location.href = 'home.html';</script>";
                    exit();
                }

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Successful login - log attempt
                    $stmt = $conn->prepare("INSERT INTO login_attempts (email, success) VALUES (?, 1)");

                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['name'] = $user['name'];

                    if ($user['user_type'] === 'Buyer') {
                        header("Location: buyersdisplay.php");
                        exit();
                    } elseif ($user['user_type'] === 'Seller') {
                        // Check shop status
                        $checkShop = "SELECT * FROM shops WHERE user_id = ?";
                        $stmt2 = $conn->prepare($checkShop);
                        $stmt2->bind_param("i", $user['id']);
                        $stmt2->execute();
                        $shopResult = $stmt2->get_result();

                        if ($shopResult->num_rows > 0) {
                            $shop = $shopResult->fetch_assoc();

                            if ($shop['status'] === 'deleted') {
                                echo "<script>alert('Your shop has been deleted. Please contact support.'); window.location.href = 'home.html';</script>";
                                exit();
                            } else {
                                $_SESSION['shop_id'] = $shop['shop_id'];
                                header("Location: shopsuccess.php");
                                exit();
                            }
                        } else {
                            header("Location: sellers.php"); // No shop yet
                            exit();
                        }
                    }
                } else {
                    // Invalid password - log failed attempt
                    $stmt = $conn->prepare("INSERT INTO login_attempts (email, success) VALUES (?, 0)");
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->close();

                    $error = "Invalid password.";
                }
            } else {
                // User not found - log failed attempt
                $stmt = $conn->prepare("INSERT INTO login_attempts (email, success) VALUES (?, 0)");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->close();

                $error = "Invalid name, email, or user type.";
            }
        }
    }
    $conn->close();
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
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
        .neumorphic-button:hover { background: #45a049; }
        a {
            display: block;
            margin: 15px 0;
            color: #007bff;
            text-decoration: none;
        }
        a:hover { text-decoration: underline; }
    </style>
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
            <label><input type="radio" name="user_type" value="Buyer" required> Buyer</label>
            <label><input type="radio" name="user_type" value="Seller" required> Seller</label>
        </div>

        <div style="margin: 10px 0;">
            <img src="captcha.php" alt="CAPTCHA Image"><br>
            <input type="text" name="captcha" class="neumorphic-input" placeholder="Enter CAPTCHA" required>
        </div>

        <button type="submit" class="neumorphic-button">Login</button>
        <a href="forgot_password.php">Forgot Password?</a>
        
    </form>
</div>

</body>
</html>