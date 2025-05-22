<?php
// Include PHPMailer classes
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'db_config.php';

    $shop_name = $_POST['shop_name'];
    $owner_name = $_POST['owner_name'];
    $gst_no = $_POST['gst_no'];
    $shop_address = $_POST['shop_address'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];

    $image_field_logo= "shop_logo";
    $shop_logo = null;
    if (isset($_FILES['shop_logo']) && is_uploaded_file($_FILES['shop_logo']['tmp_name'])) {
    $shop_logo = file_get_contents($_FILES['shop_logo']['tmp_name']);
      }

    // Prepare insert query with placeholder for blob
    $stmt = $conn->prepare("INSERT INTO shops 
    (user_id, shop_name, owner_name, gst_no, shop_address, contact_number, email, shop_logo) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind parameters, note 's' for strings and 'i' for integers
    // 'shop_logo' will be bound separately as blob data
    if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

    $stmt->bind_param(
      "isssssss", 
      $user_id, 
      $shop_name, 
      $owner_name,
      $gst_no, 
      $shop_address, 
      $contact_number, 
      $email, 
      $shop_logo
    );

    if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

  $shop_id = $conn->insert_id;

// Create a new PHPMailer instance
$mail = new PHPMailer();

// Server settings
$mail->isSMTP();
$mail->Host       = 'smtp.gmail.com'; // Use your SMTP server
$mail->SMTPAuth   = true;
$mail->Username   = 'vbm5274@gmail.com'; // Your Gmail ID
$mail->Password   = 'mpngsriasrltmsoz';    // Your Gmail App Password (not normal password)
$mail->SMTPSecure = 'tls';                  
$mail->Port       = 587;

// Recipients
$mail->setFrom('vbm5274@gmail.com', 'Silkora Team');
$mail->addAddress($email, $owner_name); // Send mail to shop owner's email

// Content
$mail->isHTML(true);
$mail->Subject = 'Welcome to Silk Aura - Your Shop is Registered!';

$mail->Body = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body style='font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;'>
  <table align='center' border='0' cellpadding='0' cellspacing='0' width='100%' style='max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 8px;'>
    <tr>
      <td align='center' style='padding: 20px 0;'>
        <h2 style='color: #4f46e5;'>Welcome to Silk Aura!</h2>
        <p style='font-size: 16px; color: #333;'>Hi <strong>{$owner_name}</strong>,</p>
        <p style='font-size: 16px; color: #333;'>We're excited to let you know that your shop <strong>{$shop_name}</strong> has been successfully registered on the Silkora platform.</p>
      </td>
    </tr>
  </table>
</body>
</html>
";

// Send the email
if (!$mail->send()) {
    error_log('Mailer Error: ' . $mail->ErrorInfo); // Log the error (optional)
}

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Display a success message and redirect to the shop details page
echo "
  <div style='
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    padding: 30px 40px;
    text-align: center;
    z-index: 9999;
    font-family: sans-serif;
    min-width: 300px;
  '>
    <div style='color: green; font-size: 40px; margin-bottom: 10px;'>
      <i class='fas fa-check-circle'></i>
    </div>
    <p style='margin: 10px 0 0; font-size: 16px; font-weight: bold;'>Shop registration details submitted</p>
    <p style='font-size: 13px; color: gray;'>Redirecting...</p>
  </div>

  <script src='https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js'></script>
  <script>
    const duration = 2 * 1000;
    const animationEnd = Date.now() + duration;
    const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 1000 };

    const interval = setInterval(function () {
      const timeLeft = animationEnd - Date.now();
      if (timeLeft <= 0) return clearInterval(interval);

      const particleCount = 50 * (timeLeft / duration);
      confetti({
        ...defaults,
        particleCount,
        origin: { x: Math.random(), y: Math.random() - 0.2 }
      });
    }, 200);

    setTimeout(function() {
      window.location.href = 'shopsuccess.php?shop_id={$shop_id}';
    },30000);
  </script>

  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css' />
";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register Shop | Silk Auro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f0f0;
      margin: 0;
      padding: 0;
    }
    header {
      background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
      color: white;
      padding: 20px;
      text-align: center;
      font-size: 24px;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 50px;
    }
    header h1 {
      margin: 0;
      font-size: 36px;
      font-weight: bold;
    }
    header p {
      font-size: 18px;
      margin-top: 5px;
    }
    .container {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 10px;
    }
    .form-container {
      background-color: white;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      border-radius: 12px;
      padding: 40px;
      width: 100%;
      max-width: 600px;
    }
    .form-title {
      font-size: 24px;
      font-weight: bold;
      text-align: center;
      color: #4a4a4a;
      margin-bottom: 20px;
    }
    .input-group {
      margin-bottom: 20px;
    }
    .input-group label {
      font-size: 14px;
      color: #555;
      margin-bottom: 8px;
      display: block;
    }
    .input-group input,
    .input-group textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      box-sizing: border-box;
    }
    .input-group input[type="file"] {
      padding: 10px;
    }
    .button {
      background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
      color: white;
      padding: 12px 30px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      width: 100%;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .button:hover {
      background-color: #ff9a8b;
    }
  </style>
</head>
<body>

  <header>
    <div>
      <h1>Silk Auro</h1>
      
    </div>
  </header>

  <div class="container">
    <div class="form-container">
      <h2 class="form-title">Register Your Shop</h2>

      <form action="" method="POST" enctype="multipart/form-data">
        <div class="input-group">
          <label for="shop_name">Shop Name</label>
          <input type="text" id="shop_name" name="shop_name" required placeholder="Auro Silks" />
        </div>

        <div class="input-group">
          <label for="shop_logo">Shop Logo</label>
          <input type="file" id="shop_logo" name="shop_logo" accept="image/*" required />
        </div>

        <div class="input-group">
          <label for="owner_name">Owner Name</label>
          <input type="text" id="owner_name" name="owner_name" required placeholder="Ramesh Kumar" />
        </div>

        <div class="input-group">
          <label for="gst_no">GST Number</label>
          <input type="text" id="gst_no" name="gst_no" required placeholder="29ABCDE1234F1Z5" />
        </div>

        <div class="input-group">
          <label for="shop_address">Shop Address</label>
          <textarea id="shop_address" name="shop_address" required placeholder="Full address" rows="3"></textarea>
        </div>

        <div class="input-group">
          <label for="contact_number">Contact Number</label>
          <input type="tel" id="contact_number" name="contact_number" required placeholder="9876543210" />
        </div>

        <div class="input-group">
          <label for="email">Email (optional)</label>
          <input type="email" id="email" name="email" placeholder="email@example.com" />
        </div>

        <button type="submit" class="button">Register Shop</button>
      </form>
    </div>
  </div>

</body>
</html>