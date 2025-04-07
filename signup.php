<?php 
include 'db_config.php';  // Include your MySQLi connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $shop_name = $_POST['shop_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $gst = $_POST['gst'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);  // Hash the password
    $user_type = $_POST['user_type'];

    // Prepare the SQL query
    $query = "INSERT INTO users (name, email, shop_name, phone, address, gst, password, user_type) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);

    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("ssssssss", $name, $email, $shop_name, $phone, $address, $gst, $password, $user_type);
        
        if ($stmt->execute()) {
            // ✅ PHP Redirection
            if ($user_type == 'Buyer') {
                header("Location: buyers.html");
                exit();
            } else {
                header("Location: sellers.html");
                exit();
            }
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
