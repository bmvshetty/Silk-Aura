<?php
session_start();
$conn = new mysqli("localhost", "root", "", "silk_aura");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get buyer_id from session
$buyer_id = $_SESSION['user_id'] ?? $_SESSION['buyer_id'] ?? null;
if (!$buyer_id) die("User not logged in.");

// Step 1: Handle placing final order
if (isset($_POST['final_place_order'])) {
    // 1. Get the latest product_id from the placed cart
    $stmt = $conn->prepare("SELECT product_id FROM orders WHERE buyer_id = ? AND order_status = 'cart' ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $stmt->bind_result($product_id);
    $stmt->fetch();
    $stmt->close();

    // 2. Set it to session so success.php can access it
    $_SESSION['product_id'] = $product_id;

    // 3. Now update status to 'placed'
    $stmt2 = $conn->prepare("UPDATE orders SET order_status = 'placed' WHERE buyer_id = ? AND order_status = 'cart'");
    $stmt2->bind_param("i", $buyer_id);
    $stmt2->execute();
    $stmt2->close();

    // 4. Redirect to success
    header("Location: success.php?payment_id=abc123&amount=500&type=Full&product_id=$product_id");
    exit();
}

// Step 2: Handle adding to cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['product_id']) && isset($_POST['order_type'])) {
    $product_id = $_POST['product_id'];
    $shop_id = $_POST['shop_id'];
    $order_type = $_POST['order_type'];

    // Set quantity and price based on order type
    if ($order_type == 'sample') {
        $quantity = 1; // Fixed quantity for sample (you can consider it 1 unit of sample)
        $price = 300;  // Fixed price for sample
    } else {
        $quantity = $_POST['quantity']; // Regular quantity input
        $price = $_POST['price'];       // Regular price input
    }

    $stmt = $conn->prepare("INSERT INTO orders (product_id, buyer_id, shop_id, order_type, order_status, quantity, price) 
                            VALUES (?, ?, ?, ?, 'cart', ?, ?)");
    $stmt->bind_param("iiisii", $product_id, $buyer_id, $shop_id, $order_type, $quantity, $price);
    $stmt->execute();
    $stmt->close();

    // Ensure there is no output before the header
    header("Location: border.php?success=1");
    exit();
}

// Step 3: Fetch all 'cart' orders
$query = "
    SELECT s.*, o.id AS order_id, o.order_type, s.image_paths AS product_image
    FROM selleritem s
    JOIN orders o ON s.id = o.product_id
    WHERE o.buyer_id = $buyer_id AND o.order_status = 'cart'
    ORDER BY s.shop_id DESC
";
$products = $conn->query($query);
$num_rows = $products->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Order Silk Products | Silk Aura</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
      background-color: #fff0f3;
    }

    .top-container {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px 40px;
      background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      font-size: 2rem;
      font-weight: bold;
      color: white;
    }

    .product-list {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .card {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 20px;
      background: #fff;
      border-radius: 15px;
      padding: 20px;
      margin: 20px 40px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      flex-wrap: wrap;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }

    .card img,
    .image-placeholder {
      width: 100px;
      height: 100px;
      border-radius: 12px;
      object-fit: cover;
      flex-shrink: 0;
      border: 2px solid #ff9a8b;
    }

    .image-placeholder {
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #e5e7eb;
      font-size: 0.9rem;
      color: #4b5563;
    }

    .card-content {
      flex: 1;
      min-width: 200px;
    }

    .product-title {
      font-size: 1.25rem;
      font-weight: 600;
      color: #be185d;
      margin-bottom: 0.5rem;
    }

    .product-text {
      font-size: 0.875rem;
      color: #4b5563;
      margin: 0.25rem 0;
    }

    .card-buttons {
      display: flex;
      flex-direction: column;
      gap: 10px;
      justify-content: center;
      align-items: flex-end;
    }

    .cancel-btn,
    .place-btn {
      padding: 10px 20px;
      border: none;
      border-radius: 50px;
      cursor: pointer;
      font-size: 1rem;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .cancel-btn {
      background-color: #e5e7eb;
      color: #000;
    }

    .place-btn {
      background: linear-gradient(135deg, #16a34a, #22c55e);
      color: white;
    }

    .cancel-btn:hover,
    .place-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .status-message {
      text-align: center;
      margin: 20px;
      font-weight: bold;
      color: #2563eb;
    }
  </style>
</head>
<body>
  <div class="top-container">SILK AURA</div>

  <?php if ($num_rows > 0): ?>
    <div class="product-list">
      <?php while ($row = $products->fetch_assoc()): ?>
        <div class="card">
          <?php 
    if (!empty($row['image_paths'])) {
        $image_data = $row['image_paths']; // BLOB
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $image_type = $finfo->buffer($image_data); // get MIME type
        $base64 = base64_encode($image_data);
        echo '<img src="data:' . $image_type . ';base64,' . $base64 . '" alt="Product Image" style="width:100px; height:100px; object-fit:cover; border-radius:12px;">';
    } else {
        echo '<div class="image-placeholder">No Image</div>';
    }
  ?>

          <div class="card-content">
            <h2 class="product-title"><?= htmlspecialchars($row['item_name']) ?></h2>

            <?php 
            $order_type_lower = strtolower(trim($row['order_type']));

            if ($order_type_lower === 'sample') {
                $quantity = '250 gm'; 
                $amount = '₹300'; 
                $final_amount = 300;
            } else {
                $quantity = htmlspecialchars($row['quantity']);
                $amount = '₹' . htmlspecialchars($row['price']);
                $final_amount = $row['price'];
            }
            ?>

            <p class="product-text"><strong>Qty:</strong> <?= $quantity ?></p>
            <p class="product-text"><strong>Price:</strong><?= $amount ?></p>
            <p class="product-text"><strong>Type:</strong> <?= htmlspecialchars($row['order_type']) ?></p>
          </div>

          <div class="card-buttons">
            <form action="payment.php" method="POST">
              <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">             
              <input type="hidden" name="amount" value="<?= $final_amount ?>">
              <input type="hidden" name="product_type" value="<?= $row['order_type'] ?>">
              <input type="hidden" name="quantity" value="<?= $row['quantity'] ?>">
              <button type="submit" class="place-btn">Place Order</button>
            </form>
            <form action="cancel_order.php" method="POST">
              <input type="hidden" name="order_id" value="<?= $row['order_id'] ?>">
              <button type="submit" class="cancel-btn">Cancel</button>
            </form>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="status-message">No products in cart.</p>
  <?php endif; ?>
</body>
</html>
