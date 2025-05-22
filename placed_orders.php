<?php
session_start();

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$buyer_id = $_SESSION['user_id'] ?? $_SESSION['buyer_id'] ?? 0;
echo "<p>DEBUG Buyer ID = $buyer_id</p>";

if ($buyer_id == 0) {
    echo "<p style='color:red;'>❌ Session does NOT contain buyer_id. Go to login page first.</p>";
    exit;
}


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get Buyer ID from session
$buyer_id = $_SESSION['user_id'] ?? $_SESSION['buyer_id'] ?? 0;


$conn = new mysqli("localhost", "root", "", "silk_aura");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT * FROM placed_orders WHERE users_id = $buyer_id");
echo "<p>DEBUG Found Orders = " . $result->num_rows . "</p>";

// Fetch latest placed order for this buyer
$sql = "SELECT 
            s.item_name,
            po.order_type,
            po.amount_paid,
            po.payment_id,
            po.order_date,
            sh.shop_name,
            sh.shop_address
        FROM placed_orders po
        JOIN selleritem s ON po.product_id = s.id
        JOIN shops sh ON po.shop_id = sh.shop_id
        WHERE po.users_id = ?
        ORDER BY po.order_date DESC LIMIT 1";


$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation | SilkAura</title>
</head>
<body>
    <h2>Order Confirmation</h2>

    <?php if ($row = $result->fetch_assoc()): ?>
        <p><strong>Item:</strong> <?= htmlspecialchars($row['item_name']) ?></p>
        <p><strong>Order Type:</strong> <?= htmlspecialchars($row['order_type']) ?></p>
        <p><strong>Amount Paid:</strong> ₹<?= htmlspecialchars($row['amount_paid']) ?></p>
        <p><strong>Payment ID:</strong> <?= htmlspecialchars($row['payment_id']) ?></p>
        <p><strong>Order Date:</strong> <?= htmlspecialchars($row['order_date']) ?></p>
        <p><strong>Shop Name:</strong> <?= htmlspecialchars($row['shop_name']) ?></p>
        <p><strong>Shop Address:</strong> <?= htmlspecialchars($row['shop_address']) ?></p>
    <?php else: ?>
        <p>No recent order found for your account.</p>
    <?php endif; ?>

    <a href="my_order.php">View All Orders</a>
</body>
</html>
