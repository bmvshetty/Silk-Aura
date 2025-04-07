<?php
include 'db_config.php'; 

$message = "";
$popup_message = "";
$show_popup = false;
$show_success_popup = false;

// ✅ Form Submission for validation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['validate'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];

    // ✅ Validate User Details
    $stmt = $conn->prepare("SELECT * FROM users WHERE name = ? AND email = ? AND user_type = ?");
    $stmt->bind_param("sss", $name, $email, $user_type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        // ✅ User found, show password reset popup
        $message = "Validation successful. Please enter your new password.";
        $show_popup = true;
    } else {
        $message = "Invalid details. Please try again.";
    }
    $stmt->close();
}

// ✅ Reset Password Logic with Popup and Close Button Redirection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password === $confirm_password) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            // ✅ Success message and show popup
            $popup_message = "Password reset successful!";
            $show_success_popup = true;
        } else {
            $popup_message = "Error updating password. Try again.";
            $show_popup = true;
        }
        $stmt->close();
    } else {
        $popup_message = "Passwords do not match.";
        $show_popup = true;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
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
        .error, .message { color: red; }
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

        /* ✅ Popup Box */
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ffffff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            padding: 40px;
            z-index: 999;
            width: 400px;
            border-radius: 15px;
            text-align: center;
        }
        .popup h2 {
            margin-bottom: 20px;
        }
        .popup .close-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }
        .popup .close-btn:hover {
            background: #45a049;
        }
        .popup input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 10px;
            box-shadow: inset 5px 5px 10px #a3b1c6, inset -5px -5px 10px #ffffff;
            border: none;
        }
    </style>
</head>
<body>

<!-- ✅ Forgot Password Form -->
<div class="neumorphic-card">
    <h1>Forgot Password</h1>

    <?php if (!empty($message)): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="text" name="name" class="neumorphic-input" placeholder="Full Name" required>
        <input type="email" name="email" class="neumorphic-input" placeholder="Email" required>
        
        <div>
            <label><input type="radio" name="user_type" value="buyer" required> Buyer</label>
            <label><input type="radio" name="user_type" value="seller" required> Seller</label>
        </div>

        <button type="submit" name="validate" class="neumorphic-button">Validate</button>
    </form>
</div>

<!-- ✅ Password Reset Popup -->
<div id="reset-password-popup" class="popup" style="display: <?= $show_popup ? 'block' : 'none'; ?>;">
    <h2>Reset Password</h2>

    <form method="POST" action="">
        <input type="hidden" name="email" value="<?= $email ?>">
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>

        <button type="submit" name="reset_password" class="neumorphic-button">Reset</button>
        <button type="button" class="close-btn" onclick="document.getElementById('reset-password-popup').style.display='none'">Close</button>
    </form>
</div>

<!-- ✅ Success Message Popup with Close and Redirect -->
<div id="success-message-popup" class="popup" style="display: <?= $show_success_popup ? 'block' : 'none'; ?>;">
    <h2>Success</h2>
    <div class="message" style="color: green; font-weight: bold; margin-bottom: 15px;">
        <?= $popup_message ?>
    </div>

    <!-- ✅ Close button with redirect -->
    <button type="button" class="close-btn" onclick="redirectToLogin()">Close</button>
</div>

<script>
    // ✅ Redirect to login page after clicking "Close"
    function redirectToLogin() {
        window.location.href = 'login.php';
    }
</script>

</body>
</html>
