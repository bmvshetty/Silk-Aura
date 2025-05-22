<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "silk_aura");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$shop_id = $_GET['shop_id'] ?? null;
$shop_found = false;

if ($shop_id) {
    $stmt = $conn->prepare("SELECT * FROM shops WHERE user_id = ? AND shop_id = ?");
    $stmt->bind_param("ii", $user_id, $shop_id);
} else {
    // No specific shop selected — select the first shop owned by user
    $stmt = $conn->prepare("SELECT * FROM shops WHERE user_id = ? AND status != 'deleted' LIMIT 1");
    $stmt->bind_param("i", $user_id);
}


$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $shop = $result->fetch_assoc();
    $shop_id = $shop['shop_id'];
    $_SESSION['shop_id'] = $shop_id;
    $shop_found = true;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop Details | Silk Aura</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store" />
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        header {
            background:linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px 40px;
            color: white;
            font-size: 2.5em;
            font-weight: bold;
        }
        .logout-btn {
            background: white;
            color: #ff7e5f;
            border: none;
            padding: 10px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 25px;
            cursor: pointer;
            transition: 0.3s;
        }
        .logout-btn:hover {
            background: #ff7e5f;
            color: white;
        }
        section, nav {
            background: white;
            margin: 20px auto;
            padding: 20px;
            max-width: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .shop-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-start;
        }
        .shop-logo {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border: 2px solid orange;
            border-radius: 50%;
        }
        .shop-info p {
            margin: 6px 0;
            font-size: 16px;
            color: #555;
        }
        nav {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .nav-btn {
            background: linear-gradient(to right, #ff7e5f, orange);
            color: white;
            padding: 20px 40px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 30px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
            font-size: 18px;
        }
        .nav-btn:hover {
            transform: scale(1.05);
        }
        .product {
            position: relative;
            display: flex;
            gap: 20px;
            justify-content: flex-start;
            align-items: center;
            padding: 30px;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            border-radius: 10px;
            margin: 20px auto;
            max-width: 100%;
            width: 800px;
        }
        .product img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }
        .product-details {
            flex-grow: 1;
            text-align: left;
        }
        .product-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }
        .view-btn {
            background: linear-gradient(to right, #ff7e5f, orange);
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            margin-top: 15px;
            display: inline-block;
        }
        .view-btn:hover {
            background: linear-gradient(to right, #ff7e5f, pink);
        }
        .no-products {
            color: #999;
            font-size: 18px;
            text-align: center;
        }
        .ellipsis-btn {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            background: none;
            border: none;
            color: #555;
        }
        .delete-btn {
            display: none;
            position: absolute;
            top: 55px;
            right: 20px;
            background-color: red;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
</head>
<body>

<header>
    SILK AURA
    <button onclick="logout()" class="logout-btn">
        <a href="/logout.php" style="text-decoration: none;">Logout</a>
    </button>
</header>

<?php if ($shop_found): ?>
<section class="shop-details">
    <?php
    $logo_data = $shop['shop_logo'];
    $base64_logo = base64_encode($logo_data);
    echo '<img src="data:image/jpeg;base64,' . $base64_logo . '" alt="Shop Logo" class="shop-logo" />';
    ?>
    <div class="shop-info">
        <h2><?php echo htmlspecialchars($shop['shop_name']); ?></h2>
        <p><strong>Owner Name:</strong> <?php echo htmlspecialchars($shop['owner_name']); ?></p>
        <p><strong>GST Number:</strong> <?php echo htmlspecialchars($shop['gst_no']); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($shop['shop_address']); ?></p>
        <p><strong>Contact:</strong> <?php echo htmlspecialchars($shop['contact_number']); ?></p>
        <?php if (!empty($shop['email'])): ?>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($shop['email']); ?></p>
        <?php endif; ?>
    </div>
</section>
<?php else: ?>
<section>
    <p class="no-products">Shop not found. Please try registering again.</p>
</section>
<?php endif; ?>

<nav>
    <a class="nav-btn" href="addproduct.php?shop_id=<?php echo $shop_id; ?>">Add Items</a>
    <a class="nav-btn" href="quantity_dashboard.php">Dashboard</a>
    <a class="nav-btn" href="seller_order.php">Orders</a>
    <a class="nav-btn" href="seller_reviews.php?shop_id=<?php echo $shop_id; ?>">View Reviews</a>
</nav>

<section>
    <h3>Added Products</h3>
    <?php
    $conn = new mysqli("localhost", "root", "", "silk_aura");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // ✅ Filter out deleted products here
    $product_query = "SELECT id, item_name, image_paths FROM selleritem WHERE shop_id = ? AND status != 'deleted'";
    $product_stmt = $conn->prepare($product_query);
    $product_stmt->bind_param("i", $shop_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();

    if ($product_result->num_rows > 0) {
        while ($product = $product_result->fetch_assoc()) {
            ?>
            <div class="product">
                <button class="ellipsis-btn" onclick="toggleDelete(this)">⋯</button>
                <button class="delete-btn" onclick="confirmDelete(<?php echo $product['id']; ?>)">Delete</button>

                <?php 
                if (!empty($product['image_paths'])) {
                    $image_data = $product['image_paths'];
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $image_type = $finfo->buffer($image_data);
                    $base64 = base64_encode($image_data);
                    echo '<img src="data:' . $image_type . ';base64,' . $base64 . '" alt="Product Image" />';
                } else {
                    echo '<p>No Image Available</p>';
                }
                ?>

                <div class="product-details">
                    <p class="product-title"><?php echo htmlspecialchars($product['item_name']); ?></p>
                    <a href="view_details.php?product_id=<?php echo $product['id']; ?>" class="view-btn">View Details</a>
                </div>
            </div>
            <?php
        }
    } else {
        echo "<p class='no-products'>No products added yet.</p>";
    }
    ?>
</section>

<script>
function toggleDelete(button) {
    const deleteBtn = button.nextElementSibling;
    deleteBtn.style.display = (deleteBtn.style.display === "block") ? "none" : "block";
}

function confirmDelete(productId) {
    if (confirm("Are you sure you want to delete this product?")) {
        window.location.href = "delete_product.php?id=" + productId;
    }
}
</script>

</body>
</html>