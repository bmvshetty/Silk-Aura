<?php
// Include PHPMailer
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
include('db_config.php');

// Redirect if not logged in as admin
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$status = "loading";
$message = "";
$success = false;

if ($user_id > 0 && isset($_GET['confirm'])) {
    // ✅ Fetch user email and name
    $fetch_user = "SELECT email, name FROM users WHERE id = ?";
    $stmt_user = mysqli_prepare($conn, $fetch_user);
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    $user_data = mysqli_fetch_assoc($result_user);
    $owner_email = $user_data['email'];
    $owner_name = $user_data['name'];

    mysqli_begin_transaction($conn);
    try {
        // Soft delete user
        $update_user = "UPDATE users SET status = 'deleted' WHERE id = ?";
        $stmt1 = mysqli_prepare($conn, $update_user);
        mysqli_stmt_bind_param($stmt1, "i", $user_id);
        if (!mysqli_stmt_execute($stmt1)) {
            throw new Exception("Failed to update user status.");
        }

        // Soft delete related shops
        $update_shops = "UPDATE shops SET status = 'deleted' WHERE user_id = ?";
        $stmt2 = mysqli_prepare($conn, $update_shops);
        mysqli_stmt_bind_param($stmt2, "i", $user_id);
        if (!mysqli_stmt_execute($stmt2)) {
            throw new Exception("Failed to update shop status.");
        }

        // Fetch all related shop_ids of the user
        $shop_ids = [];
        $shop_query = "SELECT shop_id FROM shops WHERE user_id = ?";
        $stmt3 = mysqli_prepare($conn, $shop_query);
        mysqli_stmt_bind_param($stmt3, "i", $user_id);
        mysqli_stmt_execute($stmt3);
        $result = mysqli_stmt_get_result($stmt3);
        while ($row = mysqli_fetch_assoc($result)) {
            $shop_ids[] = $row['shop_id'];
        }

        // Soft delete from selleritem and orders for each shop_id
        foreach ($shop_ids as $shop_id) {
            $stmt4 = mysqli_prepare($conn, "UPDATE selleritem SET status = 'deleted' WHERE shop_id = ?");
            mysqli_stmt_bind_param($stmt4, "i", $shop_id);
            if (!mysqli_stmt_execute($stmt4)) {
                throw new Exception("Failed to update seller items for shop_id $shop_id");
            }

            $stmt5 = mysqli_prepare($conn, "UPDATE orders SET status = 'deleted' WHERE shop_id = ?");
            mysqli_stmt_bind_param($stmt5, "i", $shop_id);
            if (!mysqli_stmt_execute($stmt5)) {
                throw new Exception("Failed to update orders for shop_id $shop_id");
            }
        }

        // ✅ Send email notification
        if (!empty($owner_email)) {
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'vbm5274@gmail.com'; // Your Gmail ID
            $mail->Password   = 'mpngsriasrltmsoz';  // App password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('vbm5274@gmail.com', 'Silk Aura Team');
            $mail->addAddress($owner_email, $owner_name);

            $mail->isHTML(true);
            $mail->Subject = 'Silk Aura - Account Deletion Notice';

            $mail->Body = "
                <h3>Dear $owner_name,</h3>
                <p>We regret to inform you that your account on <strong>Silk Aura</strong> has been deleted by the admin.</p>
                <p>If you believe this was a mistake or have questions, please contact our support team.</p>
                <br>
                <p>Regards,<br>Silk Aura Team</p>
            ";

            $mail->send();
        }

        mysqli_commit($conn);
        $status = "success";
        $message = "✅ User and related data marked as deleted.";
        $success = true;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $status = "error";
        $message = "❌ Deletion failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete User</title>
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
    <h2>Deleting user... Please wait</h2>
    <div class="loader"></div>
    <script>
        setTimeout(function () {
            window.location.href = "delete_users.php?user_id=<?php echo $user_id; ?>&confirm=1";
        }, 1500);
    </script>

<?php else: ?>
    <p class="message"><?php echo $message; ?></p>
    <a href="manage_users.php" class="btn">Go back to Manage Users</a>
<?php endif; ?>

</body>
</html>