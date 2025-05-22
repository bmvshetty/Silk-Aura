<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "silk_aura";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle release action
$successMessage = '';
if (isset($_GET['release_user_id'])) {
    $user_id = intval($_GET['release_user_id']);

    // 1. Set user status to active
    $conn->query("UPDATE users SET status = 'active' WHERE id = $user_id");

    // 2. Set related shop status to active
    $shop_result = $conn->query("SELECT shop_id FROM shops WHERE user_id = $user_id AND status = 'deleted'");
    while ($shop = $shop_result->fetch_assoc()) {
        $shop_id = $shop['shop_id'];

        // Update shop status
        $conn->query("UPDATE shops SET status = 'active' WHERE shop_id = $shop_id");

        // Update related products and orders
        $conn->query("UPDATE selleritem SET status = 'active' WHERE shop_id = $shop_id");
        $conn->query("UPDATE orders SET status = 'active' WHERE shop_id = $shop_id");
    }

    // Set success flag
    $successMessage = "User and related data released successfully!";
}

// Fetch deleted users
$user_result = $conn->query("SELECT * FROM users WHERE status = 'deleted'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Release Deleted Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        header {
            background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            width: 85%;
            margin: auto;
            padding-top: 30px;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-left: 6px solid #28a745;
            margin-bottom: 20px;
            border-radius: 5px;
            display: none;
        }
        .user-card {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
            position: relative;
        }
        .user-card h3 {
            margin: 0;
            font-size: 20px;
        }
        .user-card p {
            margin: 8px 0;
            color: #555;
        }
        .release-btn {
            background: #28a745;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .release-btn:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <header>
        <h1>Release Deleted Users</h1>
    </header>
    <div class="container">

        <div id="success" class="success-message"><?php echo $successMessage; ?></div>

        <?php
        if ($user_result->num_rows > 0) {
            while ($user = $user_result->fetch_assoc()) {
        ?>
            <div class="user-card">
                <h3><?php echo htmlspecialchars($user['name']); ?> (ID: <?php echo $user['id']; ?>)</h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars($user['user_type']); ?></p>
                <p><strong>Deleted At:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>

                <form method="get" onsubmit="return confirmRelease(<?php echo $user['id']; ?>)">
                    <input type="hidden" name="release_user_id" value="<?php echo $user['id']; ?>">
                    <button type="submit" class="release-btn">Release</button>
                </form>
            </div>
        <?php
            }
        } else {
            echo "<p>No deleted users found.</p>";
        }
        ?>
    </div>

    <script>
        function confirmRelease(userId) {
            return confirm("Are you sure you want to release user ID " + userId + " and all related data?");
        }

        // Show success message if it exists
        const successBox = document.getElementById("success");
        if (successBox.innerText.trim() !== "") {
            successBox.style.display = "block";
            setTimeout(() => {
                successBox.style.display = "none";
            }, 4000);
        }
    </script>
</body>
</html>
