<?php
session_start();

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Invalid request.";
    exit();
}

$product_id = $_GET['id'];

$conn = new mysqli("localhost", "root", "", "silk_aura");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update status to 'deleted'
$stmt = $conn->prepare("UPDATE selleritem SET status = 'deleted' WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();

$success = $stmt->affected_rows > 0;
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Deleting Product...</title>
    <meta http-equiv="refresh" content="4;url=shopsuccess.php">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f3f3;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .message-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            text-align: center;
        }
        .loader {
            border: 6px solid #f3f3f3;
            border-top: 6px solid orange;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg);}
            100% { transform: rotate(360deg);}
        }
        .success {
            color: green;
            font-size: 20px;
            font-weight: bold;
        }
        .fail {
            color: red;
            font-size: 20px;
            font-weight: bold;
        }
        .redirect {
            font-size: 14px;
            color: #777;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <div class="loader"></div>
        <?php if ($success): ?>
            <div class="success">Product deleted successfully!</div>
        <?php else: ?>
            <div class="fail">Failed to delete the product.</div>
        <?php endif; ?>
        <div class="redirect">Redirecting to shop details page...</div>
    </div>
</body>
</html>
