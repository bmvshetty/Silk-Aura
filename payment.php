<?php
session_start();


// Fetch POST data sent from the form in border.php
$amount = isset($_POST['amount']) ? $_POST['amount'] : '';
$productType = isset($_POST['product_type']) ? $_POST['product_type'] : '';
$quantity = isset($_POST['quantity']) ? $_POST['quantity'] : '';

if (strtolower($productType) === 'sample') {
    $amount = 300;
    $quantity = '250 gm';
}
?>

<!DOCTYPE html>
<html>
<head>
<title>SilkAura - Buy Silk Threads</title>
<style>
     * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #fff0f3;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .neumorphic-card {
            background: #e0e5ec;
            border-radius: 20px;
            box-shadow: 10px 10px 20px #a3b1c6, -10px -10px 20px #ffffff;
            width: 450px;
            padding: 40px;
            text-align: center;
        }

        h2 {
            color: #444;
            margin-bottom: 25px;
        }

        .info-label {
            text-align: left;
            font-weight: bold;
            color: #333;
            margin: 10px 0 5px 0;
        }

        .info-value {
            background: #f0f3f7;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 15px;
            text-align: left;
            color: #555;
            font-size: 15px;
            box-shadow: inset 5px 5px 10px #a3b1c6, inset -5px -5px 10px #ffffff;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #6A1B9A;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 3px 3px 8px #a3b1c6, -3px -3px 8px #ffffff;
            transition: background-color 0.3s ease;
            margin-top: 15px;
        }

        button:hover {
            background: #4A148C;
        }
</style>
</head>
<body>
    <div class="neumorphic-card">
        <form action="pay.php" method="POST">
            <h2>Confirm Order</h2>

            <div class="amount">
                <strong>Order Amount:</strong> ₹<?php echo htmlspecialchars($amount); ?>
            </div>

            <div>
                <strong>Product Type:</strong>
            <?php echo htmlspecialchars($productType); ?>
            </div>

            <div class="quantity">
            <strong>Quantity:</strong><?php echo htmlspecialchars($quantity); ?>
            </div>

            <!-- Hidden field for the order amount -->
            <input type="hidden" name="amount" value="<?php echo htmlspecialchars($amount); ?>">

            <button type="submit">Proceed to Pay</button>
        </form>
    </div>
</body>
</html>
