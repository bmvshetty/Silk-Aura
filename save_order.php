<?php
session_start();
$conn = new mysqli("localhost", "root", "", "silk_aura");

// Set JSON header
header('Content-Type: application/json');

// Verify request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Check required parameters
if (!isset($_POST['product_id'], $_POST['shop_id'], $_POST['type'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
    exit;
}

// Validate session
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

// Sanitize and validate input
$product_id = (int)$_POST['product_id'];
$shop_id = (int)$_POST['shop_id'];
$order_type = in_array($_POST['type'], ['order', 'sample']) ? $_POST['type'] : 'order';
$buyer_id = (int)$_SESSION['user_id'];

// Database operation
try {
    // Check if item already exists in cart
    $check_stmt = $conn->prepare("SELECT id FROM orders WHERE product_id = ? AND buyer_id = ? AND order_status = 'cart'");
    $check_stmt->bind_param("ii", $product_id, $buyer_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Item already in cart']);
        exit;
    }

    // Insert new cart item
    $insert_stmt = $conn->prepare("INSERT INTO orders (product_id, buyer_id, shop_id, order_type, order_status) VALUES (?, ?, ?, ?, 'cart')");
    $insert_stmt->bind_param("iiis", $product_id, $buyer_id, $shop_id, $order_type);
    
    if (!$insert_stmt->execute()) {
        throw new Exception($insert_stmt->error);
    }
    
    echo json_encode([
        'status' => 'success', 
        'message' => 'Added to cart',
        'order_type' => $order_type,
        'product_id' => $product_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($insert_stmt)) $insert_stmt->close();
    $conn->close();
}
?>