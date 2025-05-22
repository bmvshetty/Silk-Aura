<?php
include('db_config.php');

$messages = "";

// Replace 'users' with your actual user table name, e.g., 'admins'
$tableName = "users";

$query = "SELECT id, password FROM $tableName";
$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $user_id = $row['id'];
        $password = $row['password'];

        if (strlen($password) < 60) {  // Not hashed yet
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $update = "UPDATE $tableName SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update);
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
            mysqli_stmt_execute($stmt);

            $messages .= "✅ Password for user ID $user_id has been hashed.<br>";
        } else {
            $messages .= "ℹ️ Password for user ID $user_id is already hashed.<br>";
        }
    }
    $messages .= "<br><strong>🎉 Password hashing complete! Please delete this script for security.</strong>";
} else {
    $messages = "⚠️ No user records found.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Password Hasher - Silk Aura</title>
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
            color: #444;
        }
        h1 { margin-bottom: 20px; }
        .message-box {
            background: #f7f9fc;
            padding: 15px;
            border-radius: 12px;
            box-shadow: inset 5px 5px 10px #a3b1c6, inset -5px -5px 10px #ffffff;
            height: 220px;
            overflow-y: auto;
            text-align: left;
            font-size: 0.9rem;
            line-height: 1.4em;
            color: #222;
        }
    </style>
</head>
<body>
    <div class="neumorphic-card">
        <h1>User Password Hasher</h1>
        <div class="message-box">
            <?= $messages ?>
        </div>
    </div>
</body>
</html>
