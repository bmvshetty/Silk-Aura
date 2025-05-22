<?php
session_start();
require 'db_config.php';

$success_message = '';

// Handle release request
// Handle release request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['release_shop_id'])) {
    $shop_id = intval($_POST['release_shop_id']);

    // Update shop status
    $stmt = $conn->prepare("UPDATE shops SET status = 'active' WHERE shop_id = ?");
    $stmt->bind_param("i", $shop_id);
    $stmt->execute();
    $stmt->close();

    // Update selleritem table
    $stmt = $conn->prepare("UPDATE selleritem SET status = 'active' WHERE shop_id = ?");
    $stmt->bind_param("i", $shop_id);
    $stmt->execute();
    $stmt->close();

    // Update orders table
    $stmt = $conn->prepare("UPDATE orders SET status = 'active' WHERE shop_id = ?");
    $stmt->bind_param("i", $shop_id);
    $stmt->execute();
    $stmt->close();

    // Set session message and redirect
    $_SESSION['success_message'] = "Shop and related items/orders released successfully!";
    header("Location: release_deleted_shops.php");
    exit;
}


// Fetch deleted shops
$query = "SELECT shop_id, shop_name, owner_name, shop_logo FROM shops WHERE status = 'deleted'";
$result = $conn->query($query);

// Retrieve and clear success message
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Silk Aura - Release Deleted Shops</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background: #f7f7f7;
        }

        .header {
            background: linear-gradient(90deg, #ff7e5f, #feb47b);
            padding: 20px 40px;
            color: #fff;
            font-family: 'Playfair Display', serif;
            font-size: 2em;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .success-message {
            text-align: center;
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin: 20px auto;
            width: 80%;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            transition: opacity 0.5s ease;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 30px;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
            width: 280px;
        }

        .card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-bottom: 15px;
            border-radius: 50%;
        }

        .card h2 {
            margin: 10px 0 5px;
        }

        .card p {
            color: #555;
        }

        .card form {
            margin-top: 15px;
        }

        .release-btn {
            padding: 8px 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .release-btn:hover {
            background-color: #218838;
        }
    </style>

    <script>
        // Auto-hide success message after 4 seconds
        window.addEventListener('DOMContentLoaded', () => {
            const msg = document.getElementById('successMessage');
            if (msg) {
                setTimeout(() => {
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 500); // Remove from DOM after fade
                }, 4000);
            }
        });

        function confirmRelease(form) {
            return confirm("Are you sure you want to release this shop?");
        }
    </script>
</head>
<body>

<div class="header">
    SILK AURA - Deleted Shops
</div>

<?php if (!empty($success_message)): ?>
    <div class="success-message" id="successMessage">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>

<div class="container">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card">
            <img src="uploads/<?php echo htmlspecialchars($row['shop_logo']); ?>" alt="Shop Logo">
            <h2><?php echo htmlspecialchars($row['shop_name']); ?></h2>
            <p>Owner: <?php echo htmlspecialchars($row['owner_name']); ?></p>
            <form method="POST" onsubmit="return confirmRelease(this);">
                <input type="hidden" name="release_shop_id" value="<?php echo $row['shop_id']; ?>">
                <button type="submit" class="release-btn">Release Shop</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>
