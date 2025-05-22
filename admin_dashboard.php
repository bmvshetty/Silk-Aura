<?php
session_start();
include('db_config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_name = $_SESSION['admin'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Silk Aura</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        header {
            background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            color: white;
            padding: 20px 0;
            text-align: center;
            width: 100%;
            position: relative;
        }
        .logout-button {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: white;
            color: #ff7e5f;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s, color 0.3s;
        }
        .logout-button:hover {
            background: #ff7e5f;
            color: white;
        }
        nav {
            text-align: center;
            margin-top: 20px;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
            text-align: center;
            margin: 0;
        }
        nav ul li {
            display: inline;
            margin-right: 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 20px;
            background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        nav ul li a:hover {
            background: linear-gradient(90deg, #feb47b, #ff7e5f, #ff9a8b);
        }
        .center-message {
            margin-top: 200px;
            font-size: 70px;
            font-weight: bold;
            background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <h1>SILK AURA</h1>
    <a href="admin_logout.php" class="logout-button">Logout</a>
</header>

<nav>
    <ul>
        <li><a href="admin_dash.php">Dashboard</a></li>
        <li><a href="manage_shops.php">Manage Shops</a></li>
        <li><a href="manage_users.php">Manage Users</a></li>
    </ul>
</nav>

<div class="center-message">
    Welcome, <?php echo htmlspecialchars($admin_name); ?>
</div>

</body>
</html>