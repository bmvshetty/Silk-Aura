<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "silk_aura");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all shops from the database
$query = "SELECT * FROM shops WHERE status != 'deleted'";
$result = $conn->query($query);
$conn->close();

// Check login status
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Silk Aura - buyersdisplay</title>
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
    margin-left: auto; /* This pushes the container to the right */
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

    .shop-container {
      display: flex;
      flex-wrap: wrap; /* Allow cards to wrap to next row */
      gap: 20px;
      justify-content: center; /* Optional: center them */
      padding: 20px;
    }

    .shop-card {
      flex: 0 0 350px; /* Fixed width for each card */
      display: flex;
      justify-content: space-between;
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
      width: 200px;
      height: 200px;
      border-radius: 12px;
      object-fit: cover;
      border: 2px solid #ff9a8b;
      border-radius: 50%; /* <<< This makes the image round */
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
    .guest-avatar {
  width: 40px;
  height: 40px;
  display: block;
}

.avatar-wrapper {
  background: none !important; /* Remove the gradient background */
  padding: 0 !important; /* Remove padding */
}

/* Keep the hover effect */
.avatar-wrapper:hover {
  transform: scale(1.05);
}
.shop-logo {
            width: 180px;
            height: 180px;
            object-fit: cover;
            border: 2px solid orange;
            border-radius: 8px;
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
      <button onclick="setActive(this); window.location.href='border.php';"><i class="fas fa-store"></i> Cart</button>

      
<div class="account-dropdown" id="accountDropdown">
  <div class="avatar-wrapper" onclick="toggleDropdown()">
    <svg class="guest-avatar" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="silkAuraGradient" x1="0%" y1="0%" x2="100%" y2="0%">
          <stop offset="0%" style="stop-color:#ff7e5f" />
          <stop offset="100%" style="stop-color:#feb47b" />
        </linearGradient>
      </defs>
      <circle cx="12" cy="8" r="4" fill="url(#silkAuraGradient)"/>
      <path d="M12 14c-4.42 0-8 2.69-8 6v2h16v-2c0-3.31-3.58-6-8-6z" fill="url(#silkAuraGradient)"/>
    </svg>
  </div>
        <div class="dropdown-menu" id="dropdownMenu">
          <a href="/profile.php"><i class="fas fa-user-circle"></i> Profile</a>
          <a href="/favorites.php"><i class="fas fa-heart"></i> Favorites</a>
          <a href="/my_order.php"><i class="fas fa-store"></i> Orders</a>
          <a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Shop Cards Section - Dynamic Shop Cards Loop -->
<div class="shop-container">
  <?php while ($shop = $result->fetch_assoc()): ?>
    <div class="shop-card">
       <?php
          $logo_data = $shop['shop_logo']; // binary data from DB
          $base64_logo = base64_encode($logo_data);
          echo '<img src="data:image/jpeg;base64,' . $base64_logo . '" alt="Shop Logo" class="shop-logo" />';
        ?>
        <div class="shop-info">
        <h4><?php echo htmlspecialchars($shop['shop_name']); ?></h4>
        <p>Owner: <?php echo htmlspecialchars($shop['owner_name']); ?></p>
        </div>

    <div class="shop-actions">
    <i 
  class="far fa-heart favorite-icon" 
  onclick="toggleFavorite(this)" 
  data-shop-id="<?php echo $shop['shop_id']; ?>" 
  data-shop-name="<?php echo htmlspecialchars($shop['shop_name']); ?>" 
  data-shop-owner="<?php echo htmlspecialchars($shop['owner_name']); ?>" 
  data-shop-location="<?php echo htmlspecialchars($shop['shop_address']); ?>" 
  data-shop-contact="<?php echo htmlspecialchars($shop['contact_number']); ?>" 
  data-shop-image="data:image/jpeg;base64,<?php echo base64_encode($shop['shop_logo']); ?>"></i>
</i>
      <button onclick="viewDetails(<?php echo $shop['shop_id']; ?>)">View Details</button>
     </div>
  </div>   
  <?php endwhile; ?>
</div>


  <script>
    // Pass PHP login status to JavaScript
    const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    
    // Enhanced login check function
    function requireLogin(action = 'access this feature') {
      if (!isLoggedIn) {
        alert(`Please login to ${action}`);
        window.location.href = "login.php?redirect=" + encodeURIComponent(window.location.pathname);
        return false;
      }
      return true;
    }

    // Modified all protected functions
    function toggleDropdown() {
      if (!requireLogin('access account menu')) return;
      document.getElementById("dropdownMenu").classList.toggle("active");
    }

    function toggleFavorite(icon) {
      if (!requireLogin('save favorites')) return;
      
      const shopData = {
        id: icon.dataset.shopId,
        name: icon.dataset.shopName,
        owner: icon.dataset.shopOwner,
        location: icon.dataset.shopLocation,
        contact: icon.dataset.shopContact,
        image: icon.dataset.shopImage
      };

      let favorites = JSON.parse(localStorage.getItem("favoriteShops")) || [];
      const existingIndex = favorites.findIndex(fav => fav.id === shopData.id);

      if (existingIndex !== -1) {
        favorites.splice(existingIndex, 1);
        icon.classList.remove("favorited", "fas");
        icon.classList.add("far");
      } else {
        favorites.push(shopData);
        icon.classList.add("favorited", "fas");
        icon.classList.remove("far");
      }

      localStorage.setItem("favoriteShops", JSON.stringify(favorites));
    }

    function viewDetails(shopId) {
      if (!requireLogin('view shop details')) return;
      window.location.href = "buyersdisplay2.php?shop_id=" + shopId;
    }

    // Update all event listeners
    document.addEventListener("DOMContentLoaded", function() {
      // Cart button
      document.querySelector(".action-buttons button[onclick*='border.php']").onclick = function(e) {
  if (!requireLogin('access your cart')) {
    e.preventDefault();
  } else {
    window.location.href = '/border.php'; // manually trigger the redirect
  }
};
      // Dropdown links
      document.querySelectorAll(".dropdown-menu a").forEach(link => {
        link.onclick = function(e) {
          if (!requireLogin('access ' + link.textContent.toLowerCase())) {
            e.preventDefault();
          }
        };
      });

      // Initialize favorites
      const favorites = JSON.parse(localStorage.getItem("favoriteShops")) || [];
      document.querySelectorAll(".favorite-icon").forEach(icon => {
        const shopId = icon.dataset.shopId;
        if (favorites.some(shop => shop.id === shopId)) {
          icon.classList.add("favorited", "fas");
          icon.classList.remove("far");
        }
      });

      // [Keep all other existing initialization code]
    });

    // Function for search functionality
function searchShops() {
  const searchTerm = document.getElementById('search').value.toLowerCase();
  const shopCards = document.querySelectorAll('.shop-card');
  
  shopCards.forEach(card => {
    const shopName = card.querySelector('.shop-info h4').textContent.toLowerCase();
    if (shopName.includes(searchTerm)) {
      card.style.display = 'block';
    } else {
      card.style.display = 'none';
    }
  });
}

// Attach the search function to the input field
document.getElementById('search').addEventListener('input', searchShops);
function activateMicrophone() {
  const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
  
  recognition.lang = 'en-US';
  recognition.start();
  
  recognition.onresult = function(event) {
    const speechToText = event.results[0][0].transcript;
    document.getElementById('search').value = speechToText;
    searchShops();  // Optional: automatically search after speech input
  };

  recognition.onerror = function(event) {
    alert("Error occurred in recognition: " + event.error);
  };
}

    // [Keep all other existing functions exactly the same]

  </script>
</body>
</html>