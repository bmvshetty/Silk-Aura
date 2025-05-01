<?php
session_start();

$conn = new mysqli("localhost", "root", "", "silk_aura");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$shop_id = $_GET['shop_id'] ?? 0;

$query = "SELECT * FROM shops WHERE shop_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $shop_id);
$stmt->execute();
$result = $stmt->get_result();
$shop = $result->fetch_assoc();
$stmt->close();
$conn->close();


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shop Details | Silk Aura</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-[#f9f9f9] min-h-screen flex flex-col">

  <!-- Header -->
  <header class="bg-gradient-to-r from-[#ff7e5f] via-orange-400 to-pink-400 shadow-md">
    <div class="text-center py-5">
      <h1 class="text-4xl font-bold text-white drop-shadow-lg tracking-wider">SILK AURA</h1>
    </div>
  </header>

  <!-- Shop Details Section -->
  <section class="w-full bg-white shadow-md py-6 px-8 flex flex-col md:flex-row gap-6 items-center md:items-start justify-start">
    <!-- Shop Logo -->
    <div class="flex-shrink-0">
      <img src="shop_logo/<?php echo htmlspecialchars($shop['shop_logo']); ?>" alt="Shop Logo" class="w-32 h-32 object-cover border border-orange-300 rounded-md">
    </div>

    <!-- Shop Info -->
    <div class="space-y-2 text-center md:text-left">
      <h2 class="text-2xl font-semibold text-gray-800"><?php echo htmlspecialchars($shop['shop_name']); ?></h2>
      <p class="text-sm text-gray-600"><strong>Owner Name:</strong> <?php echo htmlspecialchars($shop['owner_name']); ?></p>
      <p class="text-sm text-gray-600"><strong>GST Number:</strong> <?php echo htmlspecialchars($shop['gst_no']); ?></p>
      <p class="text-sm text-gray-600"><strong>Address:</strong> <?php echo htmlspecialchars($shop['shop_address']); ?></p>
      <p class="text-sm text-gray-600"><strong>Contact:</strong> <?php echo htmlspecialchars($shop['contact_number']); ?></p>
      <?php if (!empty($shop['email'])): ?>
        <p class="text-sm text-gray-600"><strong>Email:</strong> <?php echo htmlspecialchars($shop['email']); ?></p>
      <?php endif; ?>

      
    </div>
  </section>

  <!-- Navigation Buttons -->
<nav class="bg-gray-100 shadow-sm py-4 px-6 flex flex-wrap justify-center gap-4">

<!-- Add Items -->
<a href="/addproduct.php?shop_id=<?php echo $shop_id; ?>" 
   class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#ff7e5f] to-orange-400 text-white font-medium rounded-full shadow hover:scale-105 transition">
  <i class="fas fa-plus-circle"></i> Add Items
</a>

<!-- Dashboard -->
<a href="dashboard.php" 
   class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#ff7e5f] to-orange-400 text-white font-medium rounded-full shadow hover:scale-105 transition">
  <i class="fas fa-chart-line"></i> Dashboard
</a>

<!-- Notifications -->
<a href="notifications.php" 
   class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#ff7e5f] to-orange-400 text-white font-medium rounded-full shadow hover:scale-105 transition">
  <i class="fas fa-bell"></i> Notifications
</a>

<!-- Orders -->
<a href="orders.php" 
   class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#ff7e5f] to-orange-400 text-white font-medium rounded-full shadow hover:scale-105 transition">
  <i class="fas fa-box"></i> Orders
</a>

</nav>


</body>
</html>
