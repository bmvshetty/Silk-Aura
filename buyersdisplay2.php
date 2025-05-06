<?php
include 'db_config.php'; 
$shop_id = $_GET['shop_id'];

// Fetch shop details
$shopQuery = "SELECT * FROM shops WHERE shop_id = '$shop_id'";
$shopResult = mysqli_query($conn, $shopQuery);
$shop = mysqli_fetch_assoc($shopResult);

// Fetch product details
$productQuery = "SELECT * FROM selleritem WHERE shop_id = '$shop_id'";
$productResult = mysqli_query($conn, $productQuery);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Silk Aura - Buyers Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4; /* Light background */
        }

        .top-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px 40px;
            background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .logo {
            font-size: 3em;
            color: #fff;
            font-family: 'Playfair Display', serif;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
        }

        .shop-header {
            background-color: #fff;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .shop-name {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 5px;
        }

        .shop-details-line {
            color: #555;
            margin-bottom: 3px;
            font-size: 1.1em;
        }

        .filter-search-container {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
        }

        .filter-button {
            padding: 10px 15px;
            background-color: #e0e0e0;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
        }

        .search-bar {
            flex-grow: 1;
            display: flex;
        }

        .search-bar input[type="text"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px 0 0 5px;
            flex-grow: 1;
            font-size: 1em;
        }

        .search-bar button {
            padding: 10px 15px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            font-size: 1em;
        }

        /* Styling for the item display (you'll likely have more of this) */
        .item-container {
            background-color: #fff;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 15px;
            background-color: #f9f9f9; /* Placeholder background */
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.8em;
            color: #777;
        }

        .item-details {
            flex-grow: 1;
        }

        .item-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .item-price {
            color: #d35400;
            font-size: 1.1em;
            margin-bottom: 3px;
        }

        .item-min_kgs {
            color: #777;
            font-size: 1em;
        }

        .item-actions button {
            padding: 8px 12px;
            margin-left: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            cursor: pointer;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="top-container">
        <div class="logo">SILK AURA</div>
    </div>

    <div class="shop-header">
        <h3 class="shop-name"><?php echo $shop['shop_name']; ?></h3>
        <div class="shop-details-line">Place: <?php echo $shop['shop_address']; ?></div>
        <div class="shop-details-line">GST No: <?php echo $shop['gst_no']; ?></div>

        <div class="filter-search-container">
            <button class="filter-button"><i class="fas fa-filter"></i> Filter</button>
            <div class="search-bar">
                <input type="text" placeholder="Search...">
                <button><i class="fas fa-search"></i></button>
            </div>
        </div>
    </div>

    <div class="item-container">
<?php while($row = mysqli_fetch_assoc($productResult)) { ?>
    <div class="item">
        <div class="item-image">
            <?php if (!empty($row['image'])): ?>
                <img src="uploads/<?php echo $row['image_path']; ?>" width="100" height="100" style="border-radius:5px;" />
            <?php else: ?>
                No Image
            <?php endif; ?>
        </div>
        <div class="item-details">
            <div class="item-name"><?php echo $row['item_name']; ?></div>
            <div class="item-price">₹<?php echo $row['price']; ?>/kg</div>
            <div class="item-min_kgs"><?php echo $row['min_kgs']; ?></div>
        </div>
        <div class="item-actions">
            <button>Order</button>
            <button>Sample</button>
            <button>Cancel</button>
        </div>
    </div>
<?php } ?>
</div>


</body>
</html>