<?php
session_start();


// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page
    exit();
}
$id = intval($_SESSION['user_id']); // Use correct session variable



// ✅ Connect to database
$conn = new mysqli("localhost", "root", "", "silk_aura");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Update profile when form is submitted
if (isset($_POST['save'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $password = $conn->real_escape_string($_POST['password']);

    $update = "UPDATE users SET name='$name', email='$email', phone='$phone', address='$address', password='$password' WHERE id='$id'";
    if ($conn->query($update)) {
        $message = "Profile updated successfully.";
    } else {
        $message = "Error updating profile: " . $conn->error;
    }
}

// ✅ Fetch user data
$query = "SELECT * FROM users WHERE id='$id'";
$result = $conn->query($query);
$user = $result && $result->num_rows > 0 ? $result->fetch_assoc() : null;

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f6f6;
            margin: 0;
            padding: 40px;
        }
        .profile-container {
            max-width: 500px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #555;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1em;
        }
        textarea {
            resize: vertical;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 12px 18px;
            margin-top: 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1em;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            text-align: center;
            color: green;
            margin-top: 15px;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <h2>My Profile</h2>
    <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>
    <form method="POST">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>

        <label>Phone</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>

        <label>Address</label>
        <textarea name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>

        <label>Password</label>
        <input type="password" name="password" value="<?php echo htmlspecialchars($user['password'] ?? ''); ?>" required>

        <button type="submit" name="save">Save Changes</button>
    </form>
</div>

</body>
</html>
