<?php 
// Database Connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'silk_aura';

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$success = "";
$name = $email = $shop_name = $phone = $address = $gst = $user_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Store form data to retain on reload
    $name = $_POST['name'];
    $email = $_POST['email'];
    $shop_name = $_POST['shop_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $gst = $_POST['gst'];
    $user_type = $_POST['user_type'];

    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ✅ Password Confirmation
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // ✅ Check how many times the email is used
        $check_query = "SELECT COUNT(*) AS count FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        $email_count = $row['count'];
        $check_stmt->close();

        // ✅ Allow only two registrations per email (Buyer + Seller)
        if ($email_count >= 2) {
            $error = "This email has already registered twice (Buyer + Seller).";
        } else {
            // ✅ Check if the email + user_type already exists
            $check_combo = "SELECT * FROM users WHERE email = ? AND user_type = ?";
            $stmt_combo = $conn->prepare($check_combo);
            $stmt_combo->bind_param("ss", $email, $user_type);
            $stmt_combo->execute();
            $combo_result = $stmt_combo->get_result();

            if ($combo_result->num_rows > 0) {
                $error = "This email is already registered as a $user_type.";
            } else {
                // ✅ Hash the password before storing it
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // ✅ Insert new user data
                $query = "INSERT INTO users (name, email, shop_name, phone, address, gst, password, user_type) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($query);

                if ($stmt) {
                    $stmt->bind_param("ssssssss", $name, $email, $shop_name, $phone, $address, $gst, $hashed_password, $user_type);

                    // ✅ Redirect to login page after successful signup
                    if ($stmt->execute()) {
                        header("Location: login.php");
                        exit();
                    } else {
                        $error = "Error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error = "Error: " . $conn->error;
                }
            }
            $stmt_combo->close();
        }
    }
}
$conn->close();
?>


<html>
<head>
    <title>Sign-In Page</title>
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background: url("http://localhost/img/raw-silk-thread-14765374.webp") no-repeat center center/cover;
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
        h1 { color: #444; margin-bottom: 20px; }
        .error { color: red; }
        .neumorphic-input, .neumorphic-button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 10px;
            border: none;
            box-shadow: inset 5px 5px 10px #a3b1c6, inset -5px -5px 10px #ffffff;
        }
        .neumorphic-button {
            background: #4CAF50;
            color: white;
            cursor: pointer;
        }
        .neumorphic-button:hover {
            background: #45a049;
        }
        a {
            display: block;
            margin: 15px 0;
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="background-overlay"></div>

<div class="neumorphic-card">
    <h1>SIGN-IN</h1>

    <!-- ✅ Display error and success messages -->
    <?php if ($error): ?>
        <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="send_otp.php" method="POST">

        <input type="text" class="neumorphic-input" name="name" placeholder="Name" required 
               value="<?php echo htmlspecialchars($name); ?>">

        <input type="email" class="neumorphic-input" name="email" placeholder="Email" required 
               value="<?php echo htmlspecialchars($email); ?>">

        <input type="text" class="neumorphic-input" name="shop_name" placeholder="Shop Name" required 
               value="<?php echo htmlspecialchars($shop_name); ?>">

        <input type="tel" class="neumorphic-input" name="phone" pattern="[0-9]{10}" placeholder="Phone (10 digits)" required 
               value="<?php echo htmlspecialchars($phone); ?>">

        <input type="text" class="neumorphic-input" name="address" placeholder="Address" required 
               value="<?php echo htmlspecialchars($address); ?>">

        <input type="text" class="neumorphic-input" name="gst" placeholder="GST Number" required 
               value="<?php echo htmlspecialchars($gst); ?>">

        <input type="password" class="neumorphic-input" name="password" placeholder="Password" required>

        <input type="password" class="neumorphic-input" name="confirm_password" placeholder="Confirm Password" required>

        <label>
            <input type="radio" name="user_type" value="Seller" required> Seller
        </label>

        <label>
            <input type="radio" name="user_type" value="Buyer" required> Buyer
        </label>

        <button type="submit" class="neumorphic-button">Register</button>
    </form>
</div>

</body>
</html>
