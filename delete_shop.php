<?php
// Include PHPMailer
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
include('db_config.php');

// Redirect if not admin
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$shop_id = isset($_GET['shop_id']) ? intval($_GET['shop_id']) : 0;
$status = "loading";
$message = "";
$success = false;
$owner_email = "";
$owner_name = "";

if ($shop_id > 0 && isset($_GET['confirm'])) {
    mysqli_begin_transaction($conn);
    try {
        // Get shop owner's email and name
        $shop_result = mysqli_query($conn, "SELECT email, owner_name FROM shops WHERE shop_id = $shop_id");
        if ($shop_row = mysqli_fetch_assoc($shop_result)) {
            $owner_email = $shop_row['email'];
            $owner_name = $shop_row['owner_name'];
        }

        // Soft delete products
        $stmt1 = mysqli_prepare($conn, "UPDATE selleritem SET status = 'deleted' WHERE shop_id = ?");
        mysqli_stmt_bind_param($stmt1, "i", $shop_id);
        if (!mysqli_stmt_execute($stmt1)) {
            throw new Exception("Failed to update product status.");
        }

        // Soft delete shop
        $stmt2 = mysqli_prepare($conn, "UPDATE shops SET status = 'deleted' WHERE shop_id = ?");
        mysqli_stmt_bind_param($stmt2, "i", $shop_id);
        if (!mysqli_stmt_execute($stmt2)) {
            throw new Exception("Failed to update shop status.");
        }

        mysqli_commit($conn);
        $status = "success";
        $message = "✅ Shop and related data marked as deleted.";
        $success = true;

        // Send email notification
        if (!empty($owner_email)) {
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'vbm5274@gmail.com';
            $mail->Password   = 'mpngsriasrltmsoz';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('vbm5274@gmail.com', 'Silk Aura Team');
            $mail->addAddress($owner_email, $owner_name);

            $mail->isHTML(true);
            $mail->Subject = 'Silk Aura - Shop Deletion Notice';

            $mail->Body = "
                <h3>Dear $owner_name,</h3>
                <p>We regret to inform you that your shop on <strong>Silk Aura</strong> has been deleted by the admin.</p>
                <p>If you believe this was a mistake or want more information, please contact our support team.</p>
                <br>
                <p>Regards,<br>Silk Aura Team</p>
            ";

            $mail->send(); // Send the email
        }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $status = "error";
        $message = "❌ Deletion failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delete Shop</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding-top: 100px; background-color: #f4f4f4; }
        .loader {
            border: 16px solid #f3f3f3;
            border-top: 16px solid #3498db;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
            margin: 30px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .message {
            font-size: 22px;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

<?php if ($status === "loading"): ?>
    <h2>Deleting shop... Please wait</h2>
    <div class="loader"></div>
    <script>
        setTimeout(function () {
            window.location.href = "delete_shop.php?shop_id=<?= $shop_id ?>&confirm=1";
        }, 1500);
    </script>

<?php else: ?>
    <p class="message"><?= $message ?></p>
    <a href="manage_shops.php" class="btn">Go back to Manage Shops</a>
<?php endif; ?>

</body>
</html>