<?php
include 'db_config.php';
$order_id = $_POST['order_id'];

$stmt = $conn->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = ?");
$stmt->bind_param("i", $order_id);
if ($stmt->execute()) {
    header("Location: border.php");
    exit();
} else {
    echo "Error cancelling: " . $stmt->error;
}

?>
