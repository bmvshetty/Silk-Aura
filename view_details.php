<?php
// Get the product_id from the query string (e.g., view_details.php?product_id=123)
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : 0;

// Establish a connection to your database
$conn = new mysqli("localhost", "root", "", "silk_aura");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch product details based on the id
$query = "SELECT * FROM selleritem WHERE id = '$product_id'"; // Use 'id' as the product identifier
$result = $conn->query($query);

// Check if the product exists
if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    echo "Product not found.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Details - Silk Auro</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        header {
            background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
        }

        .product-details {
            max-width: 900px;
            margin: 30px auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .product-details h2 {
            margin-bottom: 25px;
            font-size: 26px;
            color: #444;
            text-align: center;
        }

        .product-image {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .product-image img {
            width: 100%;
            max-width: 320px;
            height: 320px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ddd;
        }

        .product-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px 40px;
            padding: 10px 0;
        }

        .product-info p {
            font-size: 16px;
            margin: 0;
        }

        .product-info strong {
            color: #555;
        }

        .back-button {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background-color: #388e3c;
        }

        @media (max-width: 768px) {
            .product-info {
                grid-template-columns: 1fr;
            }

            .product-image img {
                height: auto;
            }
        }
    </style>
</head>
<body>

<header>
    Silk Auro - Product Details
</header>

<div class="product-details">
    <h2>Product Details</h2>

    <div class="product-image">
        <?php 
        if (!empty($product['image_paths'])) {
            $image_data = $product['image_paths'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $image_type = $finfo->buffer($image_data);
            $base64 = base64_encode($image_data);
            echo '<img src="data:' . $image_type . ';base64,' . $base64 . '" alt="Product Image">';
        } else {
            echo '<p>No Image Available</p>';
        }
        ?>
    </div>

    <div class="product-info">
        <p><strong>Item Name:</strong> <?php echo $product['item_name']; ?></p>
        <p><strong>Quantity:</strong> <?php echo $product['quantity']; ?></p>
        <p><strong>Price:</strong> ₹<?php echo $product['price']; ?></p>
        <p><strong>Description:</strong> <?php echo $product['description']; ?></p>
        <p><strong>Fabric:</strong> <?php echo $product['fabric']; ?></p>
        <p><strong>Color:</strong> <?php echo $product['spec_colour']; ?></p>
        <p><strong>Usage:</strong> <?php echo $product['usage']; ?></p>
        <p><strong>Patterns:</strong> <?php echo $product['patterns']; ?></p>
        <p><strong>No. of Ply:</strong> <?php echo $product['ply']; ?></p>
        <p><strong>Denier:</strong> <?php echo $product['denier']; ?></p>
    </div>

    <div style="text-align: center;">
        <a href="shopsuccess.php?shop_id=<?php echo $product['shop_id']; ?>" class="back-button">Back to Product List</a>
    </div>
</div>

</body>
</html>
