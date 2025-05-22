<?php
include 'db_config.php'; // Database connection

$shops_query = "SELECT * FROM shops WHERE status != 'deleted'";
$shops_result = mysqli_query($conn, $shops_query);

if (!$shops_result) {
    die("Error fetching shops: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Silk Aura - Manage Shops</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            margin: 0;
        }

        header {
            background: linear-gradient(90deg, #ff7e5f, #feb47b);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .deleted-button-container {
            display: flex;
            justify-content: flex-end;
            padding: 20px 40px 0 0;
        }

        .deleted-button-container a button {
            padding: 10px 20px;
            background-color: #6c63ff;
            color: white;
            border: none;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
        }

        .shop-container {
            padding: 30px;
        }

        .shop-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            padding: 20px;
            gap: 20px;
            align-items: flex-start;
            position: relative;
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

        .shop-logo {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        .shop-details {
            flex: 1;
        }

        .shop-details h2 {
            margin: 0 0 10px;
            font-size: 24px;
            color: #333;
        }

        .shop-details p {
            margin: 5px 0;
            color: #555;
        }

        .view-btn {
            padding: 8px 18px;
            background-color: #ff7e5f;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            margin-top: 10px;
        }

        .view-btn a {
            text-decoration: none;
            color: white;
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
    <h1>Silk Aura - Manage Shops</h1>
</header>

<!-- Button to View Deleted Shops under the header, right aligned -->
<div class="deleted-button-container">
    <a href="release_deleted_shops.php">
        <button>View Deleted Shops</button>
    </a>
</div>

<!-- Shop Cards -->
<div class="shop-container">
    <?php
    while ($shop = mysqli_fetch_assoc($shops_result)) {
    $logo_data = $shop['shop_logo']; // binary data from DB
    $base64_logo = base64_encode($logo_data);
    echo "
    <div class='shop-card'>
        <img src='data:image/jpeg;base64,$base64_logo' alt='Shop Logo' class='shop-logo' />
        <div class='shop-details'>
            <h2>" . htmlspecialchars($shop['shop_name']) . "</h2>
            <p><strong>Owner:</strong> " . htmlspecialchars($shop['owner_name']) . "</p>
            <p><strong>Address:</strong> " . htmlspecialchars($shop['shop_address']) . "</p>
            <p><strong>Contact:</strong> " . htmlspecialchars($shop['contact_number']) . "</p>
            <button class='view-btn'>
                <a href='shop_details.php?shop_id={$shop['shop_id']}'>View Details</a>
            </button>
        </div>
        <div class='action-menu'>
            <button class='ellipsis-btn' onclick='toggleDropdown(" . $shop['shop_id'] . ")'>⋯</button>
            <div class='dropdown' id='dropdown-" . $shop['shop_id'] . "'>
                <a href='delete_shop.php?shop_id=" . $shop['shop_id'] . "' onclick='return confirm(\"Are you sure you want to delete this shop?\")'>Delete</a>
            </div>
        </div>
    </div>";
}

    ?>
</div>

</body>
</html>
