<?php
session_start();
include('db_config.php');

$error = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Secure login using prepared statement
    $query = "SELECT * FROM admins WHERE username = ? AND email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        // Verify hashed password
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin'] = $row['username'];
            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid username or email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Silk Aura</title>
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
        .error { color: red; margin-bottom: 10px; }
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
    </style>
</head>
<body>
    <div class="neumorphic-card">
        <h1>Admin Login</h1>
        <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" class="neumorphic-input" placeholder="Username" required>
            <input type="email" name="email" class="neumorphic-input" placeholder="Email" required>
            <input type="password" name="password" class="neumorphic-input" placeholder="Password" required>
            <button type="submit" name="login" class="neumorphic-button">Login</button>
        </form>
    </div>
</body>
</html>