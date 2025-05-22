<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Favorites - Silk Aura</title>
  <style>
    /* General body styling */
body {
  margin: 0;
  font-family: 'Roboto', sans-serif;
  background: #fafafa;
  color: #333;
  padding: 20px;
  min-height: 100vh;
}

/* Page heading */
h1 {
  text-align: center;
  font-family: 'Playfair Display', serif;
  font-size: 2.5em;
  color:rgb(250, 255, 255);
  margin-bottom: 30px;
  text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.25);
}

/* Container for shop cards */
.shop-container {
  display: flex;
  gap: 25px;
  flex-wrap: wrap;
  justify-content: center;
  padding: 0 20px 40px 20px;
  margin-top: 40px;
}

/* Individual shop card */
.shop-card {
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
  width: 350px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  cursor: default;
}

.shop-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

/* Shop logo */
.shop-logo {
  width: 180px;
  height: 180px;
  object-fit: cover;
  border-radius: 12px;
  border: 2px solid #ff9a8b;
  margin-bottom: 20px;
  box-shadow: 0 4px 10px rgba(255, 126, 95, 0.3);
}

/* Shop info text */
.shop-info {
  width: 100%;
  text-align: center;
}

.shop-info h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.6em;
  color: #ff7e5f;
  margin: 0 0 10px;
}

.shop-info p {
  margin: 6px 0;
  font-size: 1em;
  color: #666;
  line-height: 1.4;
}

/* Responsive adjustments */
@media (max-width: 400px) {
  .shop-card {
    width: 90vw;
    padding: 15px;
  }
  
  .shop-logo {
    width: 140px;
    height: 140px;
  }
}
.top-container {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px 40px;
      background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
.shop-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.shop-actions button {
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  font-size: 0.95em;
  cursor: pointer;
  transition: background 0.3s ease;
  min-width: 120px;
}

.view-btn {
  background: linear-gradient(90deg, #ff7e5f, #feb47b);
  color: white;
  font-weight: bold;
  box-shadow: 0 4px 12px rgba(255, 126, 95, 0.3);
}

.view-btn:hover {
  background: linear-gradient(90deg, #feb47b, #ff7e5f);
}

.remove-btn {
  background: #ff4d4d;
  color: white;
  font-weight: bold;
  box-shadow: 0 4px 12px rgba(255, 77, 77, 0.3);
}

.remove-btn:hover {
  background: #e60000;
}

.no-favorites {
  text-align: center;
  font-size: 1.2em;
  color: #888;
  margin-top: 60px;
}

  </style>
</head>
<body>
  <div class="top-container">
    <h1>Your Favorite Shops</h1>
  </div>
  
  <div class="shop-container" id="favoritesContainer">
    <!-- Favorite shops will be injected here -->
  </div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("favoritesContainer");
  let favorites = JSON.parse(localStorage.getItem("favoriteShops")) || [];

  function renderFavorites() {
    container.innerHTML = "";

    if (favorites.length === 0) {
      container.innerHTML = `
        <div class="no-favorites">
          <i class="fas fa-heart" style="font-size: 3em; color: #ccc; margin-bottom: 15px;"></i>
          <p>No favorite shops added yet</p>
        </div>
      `;
      return;
    }

    favorites.forEach((shop, index) => {
      const card = document.createElement('div');
      card.className = 'shop-card';

      card.innerHTML = `
        <img src="${shop.image.startsWith('data:image') ? shop.image : shop.image}" alt="Shop Logo" class="shop-logo" />
        <div class="shop-info">
          <h4>${shop.name}</h4>
          <p>Owner: ${shop.owner}</p>
        </div>
        <div class="shop-actions">
          <button class="view-btn" onclick="window.location.href='buyersdisplay2.php?shop_id=${shop.id}'">
            <i class="fas fa-store"></i> Visit details
          </button>
          <button class="remove-btn" onclick="removeFavorite(${index})">
            <i class="fas fa-trash-alt"></i> Remove
          </button>
        </div>
      `;

      container.appendChild(card);
    });
  }

  window.removeFavorite = function(index) {
    if (confirm("Are you sure you want to remove this shop from favorites?")) {
      favorites.splice(index, 1);
      localStorage.setItem("favoriteShops", JSON.stringify(favorites));
      renderFavorites();
    }
  };

  renderFavorites();
});
</script>

</body>
</html>
