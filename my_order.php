<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
header('Content-Type: text/html; charset=UTF-8');

// Database connection
$conn = new mysqli("localhost", "root", "", "silk_aura");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check user login
if (!isset($_SESSION['user_id']) && !isset($_SESSION['buyer_id'])) {
    header("Location: login.php");
    exit();
}

$buyer_id = $_SESSION['user_id'] ?? $_SESSION['buyer_id'];

// ✅ Fetch placed orders for this buyer
$sql = "SELECT 
            s.item_name,
            s.price,
            po.amount_paid,
            po.order_type,
            po.order_date,
            sh.shop_address
        FROM placed_orders po
        JOIN selleritem s ON po.product_id = s.id
        JOIN shops sh ON po.shop_id = sh.shop_id
        WHERE po.users_id = ?
        ORDER BY po.order_date DESC
        LIMIT 5";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error . "\nFull query: " . $sql);
}

$stmt->bind_param("i", $buyer_id);
if (!$stmt->execute()) {
    die("Query execution failed: " . $stmt->error);
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders | SilkAura</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f7f7;
            padding: 40px;
            color: #333;
        }

        .summary-container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            color: #4A148C;
            text-align: center;
            margin-bottom: 25px;
        }

        .product {
            padding: 15px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .product:last-child {
            border-bottom: none;
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            color: #6A1B9A;
            margin-bottom: 10px;
        }

        .detail {
            margin: 6px 0;
        }

        .detail-label {
            font-weight: bold;
            color: #444;
            margin-right: 5px;
        }

        .back-button {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 24px;
            background-color: #6A1B9A;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
            text-align: center;
        }

        .back-button:hover {
            background-color: #4A148C;
        }

        .no-orders {
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>
<div class="summary-container">
    <h2>My Recent Orders</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product">
                <div class="product-title"><?= htmlspecialchars($row['item_name']) ?></div>

                <div class="detail">
                    <span class="detail-label">Order Type:</span>
                    <span><?= htmlspecialchars($row['order_type']) ?></span>
                </div>

                <div class="detail">
                    <span class="detail-label">Price per Unit:</span>
                    <span>₹<?= htmlspecialchars(number_format($row['price'], 2)) ?></span>
                </div>

                <div class="detail">
                    <span class="detail-label">Amount Paid:</span>
                    <span>₹<?= htmlspecialchars(number_format($row['amount_paid'], 2)) ?></span>
                </div>

                <div class="detail">
                    <span class="detail-label">Order Date:</span>
                    <span><?= htmlspecialchars($row['order_date']) ?></span>
                </div>

                <div class="detail">
                    <span class="detail-label">Shop Address:</span>
                    <span><?= htmlspecialchars($row['shop_address']) ?></span>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="no-orders">
            <p>No recent placed orders found.</p>
            <p>Check if you’ve completed a payment.</p>
        </div>
    <?php endif; ?>

    <a class="back-button" href="buyersdisplay.php">Back to Home</a>
</div>
</body>
</html>
