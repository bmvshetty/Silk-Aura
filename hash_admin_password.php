<?php
include('db_config.php');

// Fetch all admins
$query = "SELECT id, password FROM admins";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $admin_id = $row['id'];
        $password = $row['password'];

        // Check if password is already hashed (simple check: hashed passwords are usually 60+ chars)
        if (strlen($password) < 60) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update hashed password in DB
            $update = "UPDATE admins SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $admin_id);
            mysqli_stmt_execute($stmt);

            echo "Password for admin ID $admin_id has been hashed.<br>";
        } else {
            // Comment this line out if you don't want this message displayed
            // echo "Password for admin ID $admin_id is already hashed.<br>";
        }
    }
    echo "<br><strong>✅ Password update completed. You can now delete this file.</strong>";
} else {
    echo "No admin records found.";
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
