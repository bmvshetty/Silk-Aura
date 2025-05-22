<?php
require('vendor/autoload.php');

use Razorpay\Api\Api;
$keyId = "rzp_test_cWGPyjjfsySwnX";
$keySecret = "6jIK9LPv0sX12F7MvHQCLoQx";



$api = new Api($keyId, $keySecret);

$amount = isset($_POST['amount']) ? (int)$_POST['amount'] * 100 : 0; // in paise
$type = $_POST['type'] ?? '';
$note = $_POST['note'] ?? '';



$orderData = [
    'receipt' => 'silkaura_rcpt_' . rand(1000, 9999),
    'amount' => $amount,
    'currency' => 'INR',
    'payment_capture' => 1
];

$order = $api->order->create($orderData);
$orderId = $order['id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout | SilkAura</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <h2>Redirecting to Razorpay...</h2>
    <script>
        var options = {
            "key": "<?php echo $keyId; ?>",
            "amount": "<?php echo $amount; ?>",
            "currency": "INR",
            "name": "SilkAura",
            "description": "Purchase of Silk Threads - <?php echo htmlspecialchars($type); ?>",
            "order_id": "<?php echo $orderId; ?>",
            "handler": function (response){
                window.location.href = "success.php?payment_id=" + response.razorpay_payment_id +
                                       "&amount=<?php echo $amount / 100; ?>" +
                                       "&type=<?php echo urlencode($type); ?>";
            },
            "theme": {
                "color": "#c288d3"
            }
        };
        var rzp = new Razorpay(options);
        rzp.open();
    </script>
</body>
</html>