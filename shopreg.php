<?php

// Include PHPMailer classes
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "silk_aura");

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get form data
    $shop_name = $_POST['shop_name'];
    $owner_name = $_POST['owner_name'];
    $gst_no = $_POST['gst_no'];
    $shop_address = $_POST['shop_address'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];

    // Handle shop logo upload
    $shop_logo = $_FILES['shop_logo']['name'];
    $logo_tmp = $_FILES['shop_logo']['tmp_name'];
    $target = "shop_logo/" . basename($shop_logo);

    // Create the directory if it doesn't exist
    if (!is_dir("shop_logo")) {
        mkdir("shop_logo");
    }

    // Move the uploaded logo to the folder
    move_uploaded_file($logo_tmp, $target);

    // Prepare the INSERT statement (No need to insert shop_id as it's auto-incremented)
    $stmt = $conn->prepare("INSERT INTO shops (shop_name, owner_name, gst_no, shop_address, contact_number, email, shop_logo) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $shop_name, $owner_name, $gst_no, $shop_address, $contact_number, $email, $shop_logo);

    // Execute the query
    $stmt->execute();

    // Get the shop_id of the inserted shop (this will be auto-incremented)
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
$mail->Subject = '🎉 Welcome to Silk Aura - Your Shop is Registered!';

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
    }, 2500);
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
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="flex justify-center items-center min-h-screen px-4">
    <div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-xl">
      <h2 class="text-2xl font-bold text-center text-indigo-700 mb-6">Register Your Shop</h2>

      <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
  <label for="shop_name" class="block text-sm font-medium text-gray-700 mb-1">Shop Name</label>
  <input type="text" id="shop_name" name="shop_name" required placeholder="Auro Silks"
    autocomplete="organization"
    class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
</div>

<div>
  <label for="shop_logo" class="block text-sm font-medium text-gray-700 mb-1">Shop Logo</label>
  <input type="file" id="shop_logo" name="shop_logo" accept="image/*" required
    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white" />
</div>

<div>
  <label for="owner_name" class="block text-sm font-medium text-gray-700 mb-1">Owner Name</label>
  <input type="text" id="owner_name" name="owner_name" required placeholder="Ramesh Kumar"
    autocomplete="name"
    class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
</div>

<div>
  <label for="gst_no" class="block text-sm font-medium text-gray-700 mb-1">GST Number</label>
  <input type="text" id="gst_no" name="gst_no" required placeholder="29ABCDE1234F1Z5"
    autocomplete="off"
    class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
</div>

<div>
  <label for="shop_address" class="block text-sm font-medium text-gray-700 mb-1">Shop Address</label>
  <textarea id="shop_address" name="shop_address" required placeholder="Full address" rows="3"
    autocomplete="street-address"
    class="w-full px-4 py-2 border border-gray-300 rounded-lg resize-none"></textarea>
</div>

<div>
  <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
  <input type="tel" id="contact_number" name="contact_number" required placeholder="9876543210"
    autocomplete="tel"
    class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
</div>

<div>
  <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
  <input type="email" id="email" name="email" required placeholder="email@example.com" ... />
</div>


        <div class="text-center pt-4">
         <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded-lg">
           Register Shop
        </button>

        </div>
      </form>
    </div>
  </div>
</body>
</html>
