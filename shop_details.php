<?php
session_start();
include('db_config.php');

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['shop_id'])) {
    echo "Shop ID not provided.";
    exit;
}

$shop_id = $_GET['shop_id'];

// Fetch shop name for header
$shop_query = "SELECT shop_name FROM shops WHERE shop_id = $shop_id";
$shop_result = mysqli_query($conn, $shop_query);
$shop = mysqli_fetch_assoc($shop_result);
$shop_name = $shop['shop_name'] ?? 'Shop';

// Fetch products
$product_query = "SELECT * FROM selleritem WHERE shop_id = $shop_id";
$product_result = mysqli_query($conn, $product_query);
$product_count = mysqli_num_rows($product_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($shop_name); ?> - Products</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
        }

        header {
            background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            padding: 30px;
            text-align: center;
            color: white;
        }

       .shop-title {
            background: linear-gradient(to right,rgb(255, 255, 255),rgb(255, 255, 254)); /* Coral & Light Salmon */
            padding: 25px 30px;
            margin-bottom: 25px;
            color: black;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            letter-spacing: 1px;
        }


        .product-container {
            padding: 70px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            padding: 30px;
            gap: 20px;
            align-items: flex-start;
            position: relative;
        }

        .product-image {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
        }

        .product-details {
            flex: 1;
        }

        .product-details h2 {
            margin: 0 0 10px;
            font-size: 20px;
            color: #333;
        }

        .product-details p {
            margin: 5px 0;
            color: #555;
        }

        .action-menu {
            position: absolute;
            top: 10px;
            right: 20px;
        }

        .ellipsis-btn {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }

        .dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 25px;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .dropdown a {
            display: block;
            padding: 8px 12px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
        }

        .dropdown a:hover {
            background-color: #f0f0f0;
        }

        .no-products-message {
            text-align: center;
            font-size: 18px;
            color: #ff0000;
            margin-top: 50px;
        }
    </style>
    <script>
        function toggleDropdown(id) {
            const menu = document.getElementById('dropdown-' + id);
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        window.onclick = function(event) {
            if (!event.target.matches('.ellipsis-btn')) {
                document.querySelectorAll('.dropdown').forEach(function(menu) {
                    menu.style.display = 'none';
                });
            }
        }
    </script>
</head>
<body>

<header>
    <h1>Shop Products</h1>
</header>

<div class="shop-title">
    <?php echo htmlspecialchars($shop_name); ?>
</div>

<?php if ($product_count > 0): ?>
    <div class="product-container">
        <?php while ($product = mysqli_fetch_assoc($product_result)): ?>
            <div class="product-card">
                <?php 
                if (!empty($product['image_paths'])) {
                    // image_paths is BLOB, so treat as raw data
                    $image_data = $product['image_paths'];

                    // Detect mime type
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $image_type = $finfo->buffer($image_data);

                    // Encode to base64
                    $base64 = base64_encode($image_data);

                    // Output image tag with base64 src
                    echo '<img src="data:' . $image_type . ';base64,' . $base64 . '" alt="Product Image" class="product-image">';
                } else {
                    echo '<p>No Image Available</p>';
                }
                ?>
                <div class="product-details">
                    <h2><?php echo htmlspecialchars($product['item_name']); ?></h2>
                    <p><strong>Price:</strong> ₹<?php echo htmlspecialchars($product['price']); ?></p>
                    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($product['quantity']); ?></p>
                    <p><strong>Fabric:</strong> <?php echo htmlspecialchars($product['fabric']); ?></p>
                    <p><strong>Colour:</strong> <?php echo htmlspecialchars($product['spec_colour']); ?></p>
                    <p><strong>Usage:</strong> <?php echo htmlspecialchars($product['usage']); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="no-products-message">
        No products available for this shop.
    </div>
<?php endif; ?>


</body>
</html>
