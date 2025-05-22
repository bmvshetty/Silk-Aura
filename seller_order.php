<?php
session_start();
include 'db_config.php';

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['shop_id'])) {
    header("Location: login.php");
    exit();
}

$shop_id = $_SESSION['shop_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_shipment') {
    $order_id = intval($_POST['order_id']);

    // Check order belongs to shop and get shipment_status, user_id and product_id
    $stmtCheck = $conn->prepare("SELECT shipment_status, users_id, product_id FROM placed_orders WHERE id = ? AND shop_id = ?");
    $stmtCheck->bind_param("ii", $order_id, $shop_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows !== 1) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found or permission denied']);
        exit;
    }

    $row = $resultCheck->fetch_assoc();

    if ($row['shipment_status'] === 'shipped') {
        echo json_encode(['status' => 'error', 'message' => 'Order already shipped']);
        exit;
    }

    $user_id = $row['users_id'];
    $product_id = $row['product_id'];

    // Get buyer email
    $stmtEmail = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmtEmail->bind_param("i", $user_id);
    $stmtEmail->execute();
    $resultEmail = $stmtEmail->get_result();
    if ($resultEmail->num_rows !== 1) {
        echo json_encode(['status' => 'error', 'message' => 'Buyer email not found']);
        exit;
    }
    $buyer = $resultEmail->fetch_assoc();
    $buyerEmail = $buyer['email'];
    $stmtEmail->close();

    // Get product name
    $stmtProduct = $conn->prepare("SELECT item_name FROM selleritem WHERE id = ?");
    $stmtProduct->bind_param("i", $product_id);
    $stmtProduct->execute();
    $resultProduct = $stmtProduct->get_result();
    if ($resultProduct->num_rows !== 1) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
        exit;
    }
    $product = $resultProduct->fetch_assoc();
    $productName = $product['item_name'];
    $stmtProduct->close();

    // Get shop name
    $stmtShop = $conn->prepare("SELECT shop_name FROM shops WHERE shop_id = ?");
    $stmtShop->bind_param("i", $shop_id);
    $stmtShop->execute();
    $resultShop = $stmtShop->get_result();
    if ($resultShop->num_rows !== 1) {
        echo json_encode(['status' => 'error', 'message' => 'Shop not found']);
        exit;
    }
    $shop = $resultShop->fetch_assoc();
    $shopName = $shop['shop_name'];
    $stmtShop->close();

    // Send email via PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'vbm5274@gmail.com';  // Your Gmail
        $mail->Password = 'mpngsriasrltmsoz';   // Your SMTP app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('vbm5274@gmail.com', 'Silk Aura Team');
        $mail->addAddress($buyerEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Your product is shipping now!';
        $mail->Body = "
            <h3>Dear Customer,</h3>
            <p>Your order from <strong>" . htmlspecialchars($shopName) . "</strong> for the product <strong>" . htmlspecialchars($productName) . "</strong> is now shipping.</p>
            <p>Thank you for shopping with Silk Aura!</p>
        ";

        $mail->send();

        // Update shipment_status = 'shipped'
        $stmtUpdate = $conn->prepare("UPDATE placed_orders SET shipment_status = 'shipped' WHERE id = ?");
        $stmtUpdate->bind_param("i", $order_id);
        $stmtUpdate->execute();
        $stmtUpdate->close();

        echo json_encode(['status' => 'success', 'message' => 'Shipment is done. Message has been sent to the buyer.']);
        exit;

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Mailer Error: " . $mail->ErrorInfo]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Seller Orders - Silk Aura</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f1f1f1;
    margin: 0; padding: 0;
}
header {
    background: linear-gradient(to right, #ff7e5f, pink);
    color: white;
    padding: 15px 30px;
    text-align: center;
    font-size: 24px;
}
.container {
    padding: 20px 40px;
}
h2 {
    text-align: center;
    margin-bottom: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
th, td {
    padding: 12px 15px;
    border: 1px solid #ddd;
    text-align: center;
    vertical-align: middle;
}
th {
    background: linear-gradient(to right,rgba(219, 154, 25, 0.81), pink);
    color: white;
}
tr:hover {
    background-color: #f5f5f5;
}
.no-orders {
    text-align: center;
    margin-top: 30px;
    color: #777;
}
.product-img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
    margin-bottom: 5px;
}
.btn-shipment {
    padding: 7px 15px;
    background-color: #ff7e5f;
    border: none;
    color: white;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}
.btn-shipment:disabled {
    background-color: gray;
    cursor: not-allowed;
}
#popupMessage {
    display: none;
    margin-top: 15px;
    background-color: #4CAF50;
    color: white;
    padding: 15px 25px;
    border-radius: 5px;
    font-size: 18px;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    max-width: 600px;
    text-align: center;
}
button:disabled {
    background-color: grey;
    cursor: not-allowed;
}
</style>
</head>
<body>

<header>Seller Orders - Silk Aura</header>

<div class="container">
  <h2>Your Shop Orders</h2>

  <?php
  $stmt = $conn->prepare("
      SELECT 
          po.id AS order_id,
          po.shipment_status,
          si.item_name,
          si.image_paths,
          si.price,
          po.amount_paid,
          po.shipping_address,
          po.order_date,
          s.shop_name
      FROM placed_orders po
      JOIN selleritem si ON po.product_id = si.id
      JOIN shops s ON po.shop_id = s.shop_id
      WHERE po.shop_id = ?
      ORDER BY po.order_date DESC
  ");

  $stmt->bind_param("i", $shop_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0): ?>
  <table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse: collapse;">
    <thead>
      <tr>
        <th>Order ID</th>
        <th>Shop Name</th>
        <th>Product</th>
        <th>Price</th>
        <th>Amount Paid</th>
        <th>Shipping Address</th>
        <th>Order Date</th>
        <th>Shipment</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($order = $result->fetch_assoc()): ?>
        <tr id="row-<?= $order['order_id'] ?>">
          <td><?= htmlspecialchars($order['order_id']) ?></td>
          <td><?= htmlspecialchars($order['shop_name']) ?></td>
          <td>
            <?php 
              $base64Image = base64_encode($order['image_paths']); 
            ?>
            <img src="data:image/jpeg;base64,<?= $base64Image ?>" class="product-img" alt="Product Image"><br>
            <?= htmlspecialchars($order['item_name']) ?>
          </td>
          <td>₹<?= number_format($order['price'], 2) ?></td>
          <td>₹<?= number_format($order['amount_paid'], 2) ?></td>
          <td><?= htmlspecialchars($order['shipping_address']) ?></td>
          <td><?= htmlspecialchars($order['order_date']) ?></td>
          <td>
            <button
              class="btn-shipment"
              data-order-id="<?= $order['order_id'] ?>"
              <?= ($order['shipment_status'] === 'shipped') ? 'disabled' : '' ?>
            >
              <?= ($order['shipment_status'] === 'shipped') ? 'Shipped' : 'Send Shipment' ?>
            </button>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <div id="popupMessage"></div>

  <?php else: ?>
    <p class="no-orders">No orders found for your shop.</p>
  <?php endif; ?>

</div>

<script>
document.querySelectorAll('.btn-shipment').forEach(button => {
    button.addEventListener('click', function() {
        const orderId = this.getAttribute('data-order-id');

        if (!confirm("Are you sure you want to send shipment for order #" + orderId + "?")) {
            return; // Cancel if user presses Cancel
        }

        // Disable button immediately to avoid double clicks
        this.disabled = true;

        fetch("", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: new URLSearchParams({
                action: "send_shipment",
                order_id: orderId
            })
        })
        .then(response => response.json())
        .then(data => {
            const popup = document.getElementById('popupMessage');
            if (data.status === 'success') {
                popup.textContent = data.message;
                popup.style.display = 'block';

                // Update button text and disable permanently
                this.textContent = "Shipped";
                this.disabled = true;
            } else {
                popup.textContent = "Error: " + data.message;
                popup.style.backgroundColor = '#f44336'; // red for error
                popup.style.display = 'block';

                // Re-enable button on error
                this.disabled = false;
            }

            // Hide popup after 10 seconds
            setTimeout(() => {
                popup.style.display = 'none';
                popup.style.backgroundColor = '#4CAF50'; // reset to green for next time
            }, 10000);
        })
        .catch(err => {
            alert('Network error: ' + err);
            this.disabled = false;
        });
    });
});
</script>

</body>
</html>