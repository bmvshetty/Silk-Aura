<?php
require_once('vendor/autoload.php');  // TCPDF
require_once('db_config.php');
session_start();

// Extract variables safely
$buyer_id = $_SESSION['user_id'] ?? $_SESSION['buyer_id'] ?? ($_GET['buyer_id'] ?? 0);
$product_id = $_SESSION['product_id'] ?? 0;
$payment_id = $_GET['payment_id'] ?? '';
$amount = $_GET['amount'] ?? '';
$order_type = $_SESSION['order_type'] ?? '';
$shop_address = ''; // Initialize

if (!empty($buyer_id) && !empty($payment_id)) {
    // Fetch latest cart order for this buyer
    $fetch = $conn->prepare("SELECT * FROM orders WHERE buyer_id = ? AND order_status = 'cart' ORDER BY order_date DESC LIMIT 1");
    $fetch->bind_param("i", $buyer_id);
    $fetch->execute();
    $order_result = $fetch->get_result();

    if ($order_result->num_rows > 0) {
        $order = $order_result->fetch_assoc();
        $product_id = $order['product_id'];
        $shop_id = $order['shop_id'];
        $order_type = $order['order_type'];

        // Get shop address
        $shopQuery = $conn->prepare("SELECT shop_address FROM shops WHERE shop_id = ?");
        $shopQuery->bind_param("i", $shop_id);
        $shopQuery->execute();
        $shopRes = $shopQuery->get_result();
        if ($shopRes->num_rows > 0) {
            $shop_address = $shopRes->fetch_assoc()['shop_address'];
        }

        // Insert only if not already placed
        $check = $conn->prepare("SELECT id FROM placed_orders WHERE payment_id = ?");
        $check->bind_param("s", $payment_id);
        $check->execute();
        $checkRes = $check->get_result();

        if ($checkRes->num_rows === 0) {
            $insert = $conn->prepare("INSERT INTO placed_orders 
    (users_id, product_id, shop_id, order_type, amount_paid, payment_id, shipping_address)
    VALUES (?, ?, ?, ?, ?, ?, ?)");
$insert->bind_param("iiisdss", $buyer_id, $product_id, $shop_id, $order_type, $amount, $payment_id, $shop_address);
 $insert->execute();
        }
    }
}

// ✅ Handle PDF download only when the button is clicked

if (isset($_POST['download_receipt'])) {
    require_once('vendor/autoload.php');

    // Re-fetch the one order just paid
    $stmt = $conn->prepare("
        SELECT 
            po.order_date,
            po.amount_paid,
            po.order_type,
            po.payment_id,
            po.shipping_address,
            s.item_name,
            sh.shop_name,
            sh.shop_address
        FROM placed_orders po
        JOIN selleritem s ON po.product_id = s.id
        JOIN shops sh ON po.shop_id = sh.shop_id
        WHERE po.payment_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $payment_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    // Create PDF
    $pdf = new TCPDF();
    $pdf->SetCreator('SilkAura');
    $pdf->SetAuthor('SilkAura');
    $pdf->SetTitle('Order Receipt');
    $pdf->SetSubject('Order Receipt');
    $pdf->SetMargins(20, 20, 20);
    $pdf->AddPage();

    // Header - Company Name
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->Cell(0, 10, 'SilkAura', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 6, 'Order Receipt', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetDrawColor(0,0,0); // black color for line
    $pdf->SetLineWidth(0.8);
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->Ln(8);

    if ($order) {
        // Details table start
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell(70, 16, 'Field', 1, 0, 'C', 1);
        $pdf->Cell(110, 16, 'Details', 1, 1, 'C', 1);

        $pdf->SetFont('helvetica', '', 11);

        $fields = [
            'Payment ID'       => $order['payment_id'],
            'Order Date'       => date('d-M-Y', strtotime($order['order_date'])),
            'Item Name'        => $order['item_name'],
            'Order Type'       => ucfirst($order['order_type']),
            'Amount Paid'      => 'Rs  ' . number_format($order['amount_paid'], 2),
            'Shop Name'        => $order['shop_name'],
            'Shop Address'     => $order['shop_address'],
            'Shipping Address' => $order['shipping_address'],
        ];

        foreach ($fields as $label => $value) {
            $pdf->Cell(70, 16, $label, 1);
            $pdf->Cell(110, 16, $value, 1);
            $pdf->Ln();
        }

        $pdf->Ln(10);
        // Thank you note
        $pdf->SetFont('helvetica', 'I', 11);
        $pdf->Cell(0, 10, 'Thank you for choosing SilkAura. We appreciate your business!', 0, 1, 'C');
    } else {
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, 'No order found for this payment ID.', '', 0, 'L', true);
    }

    // Output PDF for download
    $pdf->Output('SilkAura_Order_' . $payment_id . '.pdf', 'D');
    exit;
}  

?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt | SilkAura</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .receipt-box {
            max-width: 700px;
            margin: auto;
            padding: 40px;
            border: 1px solid #eee;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            border-radius: 10px;
        }
        .title {
            color: #6A1B9A;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 4px 0;
            font-size: 16px;
        }
        .info strong {
            color: #4A148C;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2e4fa;
            text-align: left;
            padding: 10px;
        }
        td {
            padding: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 14px;
            color: #777;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        .actions button {
            background-color: #6A1B9A;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            margin: 0 10px;
            transition: background-color 0.3s ease;
        }
        .actions button:hover {
            background-color: #4A148C;
        }
    </style>
</head>
<body>

<div class="receipt-box">
    <div class="title">SilkAura Payment Receipt</div>

    <div class="info">
        <p><strong>Customer:</strong> <?= htmlspecialchars($customer_name ?? 'Valued Customer') ?></p>
        <p><strong>Payment Date:</strong> <?= date("d M Y") ?></p>
        <p><strong>Payment ID:</strong> <?= htmlspecialchars($payment_id) ?></p>
    </div>

    <table>
        <tr>
            <th>Description</th>
            <th>Order Type</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Silk Thread Purchase</td>
            <td><?= htmlspecialchars($order_type) ?></td>
            <td>₹<?= htmlspecialchars($amount) ?></td>
        </tr>
    </table>

    <div class="info">
        <p><strong>Shop Address:</strong> <?= htmlspecialchars($shop_address) ?></p>
        <p><strong>Status:</strong> Payment Successful</p>
    </div>

    <div class="actions">
        <form action="success.php?payment_id=<?= urlencode($payment_id) ?>&amount=<?= urlencode($amount) ?>&product_id=<?= urlencode($product_id) ?>" method="POST" style="display:inline;">
            <button type="submit" name="download_receipt">Download Receipt</button>
        </form>
        <form action="buyersdisplay.php" method="GET" style="display:inline;">
            <button type="submit">Go to Home</button>
        </form>
        <form action="my_order.php" method="GET" style="display:inline;">
            <button type="submit">Order Details</button>
        </form>
    </div>

    <div class="footer">
        Thank you for shopping with SilkAura. For support, contact support@silkaura.in
    </div>
</div>

</body>
</html>

