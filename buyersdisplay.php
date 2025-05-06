<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "silk_aura");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all shops from the database (without filtering by status)
$query = "SELECT * FROM shops"; // Remove shop_status condition
$result = $conn->query($query);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Silk Aura - Buyers Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    body {
      margin: 0;
      font-family: 'Roboto', sans-serif;
    }

    .top-container {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px 40px;
      background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .logo {
      font-size: 2.5em;
      color: #fff;
      font-family: 'Playfair Display', serif;
      text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
    }

    .search-container {
      display: flex;
      justify-content: flex-start;
      align-items: center;
      gap: 20px;
      padding: 20px 40px;
      background: #f1f1f1;
      flex-wrap: wrap;
    }

    .search-wrapper {
      position: relative;
      max-width: 400px;
      width: 100%;
    }

    .search-wrapper input[type="search"] {
      width: 100%;
      padding: 10px 45px 10px 15px;
      border: 1px solid #ddd;
      border-radius: 5px;
      outline: none;
      box-sizing: border-box;
      font-size: 1em;
    }

    .search-wrapper button {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      font-size: 1.2em;
      color: #ff7e5f;
    }

    .search-wrapper button:hover {
      color: #feb47b;
    }

    .action-buttons {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
    }

    .action-buttons button {
      padding: 12px 20px;
      background: linear-gradient(135deg, #ff7e5f, #feb47b);
      border: none;
      border-radius: 50px;
      color: white;
      font-size: 1.1em;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .action-buttons button:hover {
      background: linear-gradient(135deg, #feb47b, #ff7e5f);
      transform: translateY(-5px);
      box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
    }

    .action-buttons button.active-nav {
      background: linear-gradient(135deg, #d35400, #e67e22);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      transform: scale(1.05);
    }

    .account-dropdown {
      position: relative;
    }

    .avatar-wrapper {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: linear-gradient(135deg, #ff7e5f, #feb47b);
      padding: 2px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .avatar-wrapper:hover {
      transform: scale(1.05);
    }

    .account-avatar {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid white;
    }

    .dropdown-menu {
      position: absolute;
      top: 55px;
      right: 0;
      background-color: white;
      border: 1px solid #ddd;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
      z-index: 100;
      opacity: 0;
      transform: translateY(0px);
      transition: all 0.3s ease;
      pointer-events: none;
      min-width: 160px;
    }

    .dropdown-menu.active {
      opacity: 1;
      transform: translateY(5px);
      pointer-events: auto;
    }

    .dropdown-menu a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 16px;
      color: #333;
      text-decoration: none;
      font-size: 0.95em;
      transition: background-color 0.2s ease;
    }

    .dropdown-menu a:hover {
      background-color: #f8f8f8;
    }

    .shop-card {
      display: flex;
      align-items: center;
      gap: 20px;
      background: #fff;
      border-radius: 15px;
      padding: 20px;
      margin: 30px 40px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
    }

    .shop-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }

    .shop-card img {
      width: 100px;
      height: 100px;
      border-radius: 12px;
      object-fit: cover;
      border: 2px solid #ff9a8b;
    }

    .shop-info {
      flex-grow: 1;
    }

    .shop-info h4 {
      font-size: 1.4em;
      margin: 0 0 8px 0;
      color: #333;
    }

    .shop-info p {
      margin: 4px 0;
      color: #666;
    }

    .shop-info .location {
      font-size: 0.9em;
      color: #999;
    }

    .shop-info .ratings {
      margin-top: 6px;
      color: #ff7e5f;
    }

    .shop-actions {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 10px;
    }

    .favorite-icon {
      font-size: 1.7em;
      color: black;
      transition: transform 0.3s ease, color 0.3s ease;
      cursor: pointer;
    }

    .favorite-icon.favorited {
      color: red;
    }

    .shop-actions button {
      background: linear-gradient(to right, #ff7e5f, #feb47b);
      border: none;
      border-radius: 25px;
      padding: 8px 16px;
      color: white;
      font-weight: bold;
      font-size: 0.9em;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .shop-actions button:hover {
      background: linear-gradient(to right, #feb47b, #ff7e5f);
      transform: scale(1.05);
    }
  </style>
</head>
<body>
  <div class="top-container">
    <div class="logo">SILK AURA</div>
  </div>

  <div class="search-container">
    <div class="search-wrapper">
      <input type="search" id="search" placeholder="Search..." />
      <button onclick="activateMicrophone()" title="Speak to search">
        <i class="fas fa-microphone"></i>
      </button>
    </div>

    <div class="action-buttons">
      <button onclick="setActive(this)"><i class="fas fa-list"></i> Requirements</button>
      <button onclick="setActive(this)"><i class="fas fa-filter"></i> Filters</button>
      <button onclick="setActive(this)"><i class="fas fa-store"></i> Shops</button>
      <button onclick="setActive(this)"><i class="fas fa-box"></i> Orders</button>

      <div class="account-dropdown" id="accountDropdown">
        <div class="avatar-wrapper" onclick="toggleDropdown()">
          <img src="https://via.placeholder.com/40" alt="User Avatar" class="account-avatar" />
        </div>
        <div class="dropdown-menu" id="dropdownMenu">
          <a href="#"><i class="fas fa-user-circle"></i> Profile</a>
          <a href="#"><i class="fas fa-heart"></i> Favorites</a>
          <a href="#"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Shop Cards Section - Dynamic Shop Cards Loop -->
<div class="shop-container">
  <?php while ($shop = $result->fetch_assoc()): ?>
    <div class="shop-card">
      <img src="shop_logo/<?php echo htmlspecialchars($shop['shop_logo']); ?>" alt="Shop Logo">
      <div class="shop-info">
        <h4><?php echo htmlspecialchars($shop['shop_name']); ?></h4>
        <p>Owner: <?php echo htmlspecialchars($shop['owner_name']); ?></p>
        <p>Location: <?php echo htmlspecialchars($shop['shop_address']); ?></p>
        <p>Contact: <?php echo htmlspecialchars($shop['contact_number']); ?></p>
      </div>
      <a href="buyersdisplay2.php?shop_id=<?php echo $shop['shop_id']; ?>" class="view-btn">View Shop</a>

    </div>
  <?php endwhile; ?>
</div>


  <script>
    function activateMicrophone() {
      alert("Microphone functionality is not implemented yet.");
    }

    function setActive(clickedBtn) {
      const buttons = document.querySelectorAll(".action-buttons button");
      buttons.forEach(button => button.classList.remove("active-nav"));
      clickedBtn.classList.add("active-nav");
    }

    function toggleDropdown() {
      document.getElementById("dropdownMenu").classList.toggle("active");
    }

    document.addEventListener("click", function (event) {
      const dropdown = document.getElementById("dropdownMenu");
      const avatar = document.getElementById("accountDropdown");

      if (!avatar.contains(event.target)) {
        dropdown.classList.remove("active");
      }
    });

    function toggleFavorite(icon) {
      icon.classList.toggle("favorited");

      if (icon.classList.contains("favorited")) {
        icon.classList.remove("far");
        icon.classList.add("fas");
        icon.style.color = "red";

        const shopData = {
          name: document.getElementById("shopName").innerText,
          item: document.getElementById("itemName").innerText,
          place: document.getElementById("place").innerText,
          location: document.getElementById("location").innerText,
          image: document.getElementById("shopImage").src
        };

        localStorage.setItem("favoriteShop", JSON.stringify(shopData));
      } else {
        icon.classList.remove("fas");
        icon.classList.add("far");
        icon.style.color = "black";

        localStorage.removeItem("favoriteShop");
      }
    }

    function viewDetails() {
      window.location.href = "buyersdisplay2.php"; // Replace with your actual details page
    }
    window.addEventListener("DOMContentLoaded", () => {
    const favorites = JSON.parse(localStorage.getItem("favoriteShops")) || [];
    const currentShop = document.getElementById("shopName").innerText;
    const icon = document.querySelector(".favorite-icon");

    const isFavorited = favorites.some(shop => shop.name === currentShop);
    if (isFavorited) {
      icon.classList.add("favorited", "fas");
      icon.classList.remove("far");
      icon.style.color = "red";
    }
  });

  // Redirect "Favorites" link to favorites page
  document.addEventListener("DOMContentLoaded", () => {
    const dropdownLinks = document.querySelectorAll(".dropdown-menu a");
    dropdownLinks.forEach(link => {
      if (link.textContent.includes("Favorites")) {
        link.href = "favorites.html"; // <-- add this to go to favorites page
      }
    });
  });
  </script>
</body>
</html>
