<?php
session_start();
include 'db_config.php';

$shop_id = $_GET['shop_id'];
$buyer_id = $_SESSION['user_id']; 
// Check if review exists
$review_exists = false;
$sql = "SELECT * FROM reviews WHERE shop_id = '$shop_id' AND buyer_id = '$buyer_id'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) > 0) {
    $review_exists = true;
}

// Get shop details
$shopQuery = "SELECT * FROM shops WHERE shop_id = '$shop_id'";
$shopResult = mysqli_query($conn, $shopQuery);
$shop = mysqli_fetch_assoc($shopResult);

// Get products for this shop
$productQuery = "SELECT * FROM selleritem WHERE shop_id = '$shop_id'";
$productResult = mysqli_query($conn, $productQuery);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $product_type = $_POST['type'];
    $product_name = $_POST['product_name'];

    $item = [
        'id'   => $product_id,
        'type' => $product_type,
        'name' => $product_name,
    ];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $_SESSION['cart'][] = $item;
    header('Location: border.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Silk Aura - Buyers Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* Your CSS styling here */
    body {
      margin: 0;
      padding: 0;
      font-family: 'Roboto', sans-serif;
      background-color: #f4f4f4;
      overflow-x: hidden;
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
      font-size: 3em;
      color: #fff;
      font-family: 'Playfair Display', serif;
      text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.3);
    }
    .shop-header {
      background-color: #fff;
      padding: 15px 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .shop-name {
      font-size: 1.8em;
      color: #333;
      margin-bottom: 5px;
    }
    .shop-details-line {
      color: #555;
      margin-bottom: 3px;
      font-size: 1.1em;
    }
    .search-cart-container {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 15px;
      margin-bottom: 20px;
    }
    .search-bar {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .search-bar input[type="text"] {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 1em;
      width: 300px;
    }
    .search-bar button {
      padding: 10px 15px;
      background-color: #f0f0f0;
      border: 1px solid #ccc;
      border-radius: 0 5px 5px 0;
      cursor: pointer;
      font-size: 1em;
    }
    .item-container {
      width: 100%;
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 30px;
      padding: 20px 0;
    }
    .item-card {
      display: flex;
      flex-direction: row;
      align-items: flex-start;
      background: #fff;
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      width: 800px;
      min-height: 240px;
      gap: 20px;
      box-sizing: border-box;
    }
    .item-image {
      width: 200px;
      height: 200px;
      object-fit: cover;
      border-radius: 10px;
      background-color: #f9f9f9;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }
    .item-details {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      font-size: 0.95em;
    }
    .item-name {
      font-size: 1.4em;
      font-weight: bold;
      color: red;
      margin-bottom: 10px;
    }
    .item-price strong,
    .item-ply strong,
    .item-denier strong {
      font-weight: bold;
      color: black;
    }
    .item-actions {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }
    .item-actions button {
      padding: 12px 15px;
      background: linear-gradient(135deg, #ff7e5f, #feb47b);
      border: none;
      border-radius: 50px;
      color: white;
      font-size: 1em;
      cursor: pointer;
    }
    .item-actions button:hover {
      background: linear-gradient(135deg, #feb47b, #ff7e5f);
      transform: translateY(-2px);
    }
    .item-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .added-to-cart {
      background-color: #28a745;
    }
  </style>

  <script defer>
    function openReviewModal() {
    <?php if ($review_exists): ?>
      alert("You have already given a review for this shop.");
    <?php else: ?>
      document.getElementById('reviewModal').style.display = 'flex';
    <?php endif; ?>
    }

    function closeReviewModal() {
      document.getElementById('reviewModal').style.display = 'none';
    }
</script>
</head>
<body>
  <div class="top-container">
    <div class="logo">SILK AURA</div>
  </div>

  <div class="shop-header">
    <h3 class="shop-name"><?php echo htmlspecialchars($shop['shop_name']); ?></h3>
    <div class="shop-details-line">GST No: <?php echo htmlspecialchars($shop['gst_no']); ?></div>
    <button onclick="openReviewModal()" style="margin-right: 20px;" class="item-actions">Review</button>
  </div>

  <div class="search-cart-container">
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Search...">
      <button><i class="fas fa-search"></i></button>
    </div>
  </div>
  
  <div class="item-container">
    <?php 
    // Loop through each product and display details
    while ($product = mysqli_fetch_assoc($productResult)) { ?>
      <div class="item-card">
        <div class="item-image">
          <?php 
          if (!empty($product['image_paths'])) {
              $image_data = $product['image_paths']; // BLOB data
              $finfo = new finfo(FILEINFO_MIME_TYPE);
              $image_type = $finfo->buffer($image_data);
              $base64 = base64_encode($image_data);
              echo '<img src="data:' . $image_type . ';base64,' . $base64 . '" alt="Product Image" style="width:100%; height:100%; object-fit:cover;">';
          } else {
              echo '<p>No Image Available</p>';
          }
          ?>
        </div>
        <div class="item-details">
          <div class="item-name"><?php echo htmlspecialchars($product['item_name']); ?></div>
          <div class="item-price"><strong>PRICE:</strong> <?php echo htmlspecialchars($product['price']); ?>₹</div>
          <div class="item-ply"><strong>PLY:</strong> <?php echo htmlspecialchars($product['ply']); ?></div>
          <div class="item-denier"><strong>DENIER:</strong> <?php echo htmlspecialchars($product['denier']); ?></div>
          <div><strong>Description:</strong> <?php echo htmlspecialchars($product['description']); ?></div>
          <div><strong>Fabric:</strong> <?php echo htmlspecialchars($product['fabric']); ?></div>
          <div><strong>Special:</strong> <?php echo htmlspecialchars($product['spec_colour']); ?></div>
          <div><strong>Usage:</strong> <?php echo htmlspecialchars($product['usage']); ?></div>
          <div><strong>Pattern:</strong> <?php echo htmlspecialchars($product['patterns']); ?></div>
          <div class="item-actions">
            <button onclick="submitProduct('<?php echo $product['id']; ?>', '<?php echo $shop_id; ?>', 'order')" id="order-btn-<?php echo $product['id']; ?>">Order</button>
            <button onclick="submitProduct('<?php echo $product['id']; ?>', '<?php echo $shop_id; ?>', 'sample')" id="sample-btn-<?php echo $product['id']; ?>">Sample</button>
          </div>
        </div>
      </div>
    <?php } // End while loop ?>
  </div>

  <div id="reviewModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background-color:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
  <div style="
    background:#fff; 
    padding:20px 30px; 
    border-radius:10px; 
    width:500px; 
    max-height:80%; 
    overflow:auto; 
    position:relative;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
  ">
    <span onclick="closeReviewModal()" 
          style="
            position:absolute; 
            top:10px; 
            right:15px; 
            cursor:pointer; 
            font-weight:bold; 
            font-size: 24px;
            color: #888;
            transition: color 0.3s;
          "
          onmouseover="this.style.color='#ff7e5f'"
          onmouseout="this.style.color='#888'">&times;</span>

    <h3 style="margin-bottom: 15px; color: #333; font-family: 'Arial', sans-serif;">Reviews for the Shop(s)</h3>

    <hr style="margin:20px 0; border-color:#eee;">

    <h3 style="margin-bottom: 15px; color: #333; font-family: 'Arial', sans-serif;">Submit Your Review</h3>

    <form method="POST" action="submit-review.php" style="font-family: 'Arial', sans-serif; color: #444;">
      <input type="hidden" name="shop_id" value="<?php echo htmlspecialchars($shop_id); ?>">

      <label for="rating" style="display:block; margin-bottom:8px; font-weight:600;">Rating (1 to 5):</label>

      <div class="star-rating" style="margin-bottom: 20px; font-size: 0; /* remove gap between inline-block */">
        <input type="radio" id="star5" name="rating" value="5" required style="display:none;">
        <label for="star5" title="5 stars" style="cursor:pointer; font-size:30px; color:#ccc; display:inline-block; transition: color 0.3s;">&#9733;</label>

        <input type="radio" id="star4" name="rating" value="4" style="display:none;">
        <label for="star4" title="4 stars" style="cursor:pointer; font-size:30px; color:#ccc; display:inline-block; transition: color 0.3s;">&#9733;</label>

        <input type="radio" id="star3" name="rating" value="3" style="display:none;">
        <label for="star3" title="3 stars" style="cursor:pointer; font-size:30px; color:#ccc; display:inline-block; transition: color 0.3s;">&#9733;</label>

        <input type="radio" id="star2" name="rating" value="2" style="display:none;">
        <label for="star2" title="2 stars" style="cursor:pointer; font-size:30px; color:#ccc; display:inline-block; transition: color 0.3s;">&#9733;</label>

        <input type="radio" id="star1" name="rating" value="1" style="display:none;">
        <label for="star1" title="1 star" style="cursor:pointer; font-size:30px; color:#ccc; display:inline-block; transition: color 0.3s;">&#9733;</label>
      </div>

      <label for="review_text" style="display:block; margin-bottom:8px; font-weight:600;">Your Review:</label>
      <textarea name="review_text" id="review_text" rows="4" cols="50" required
        style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; font-size: 1em; resize: vertical;"></textarea>

      <button type="submit" 
        style="
          margin-top: 15px;
          background: linear-gradient(135deg, #ff7e5f, #feb47b);
          border: none;
          padding: 12px 25px;
          color: white;
          font-size: 1.1em;
          border-radius: 50px;
          cursor: pointer;
          transition: background 0.3s;
        "
        onmouseover="this.style.background='linear-gradient(135deg, #feb47b, #ff7e5f)'"
        onmouseout="this.style.background='linear-gradient(135deg, #ff7e5f, #feb47b)'"
      >Submit Review</button>
    </form>
    <?php if (isset($_GET['already_reviewed'])): ?>
    <div style="color:red; margin: 10px 0;">You have already submitted a review for this shop.</div>
    <?php endif; ?>
    <?php if (isset($_GET['review_submitted'])): ?>
    <div style="color:green; margin: 10px 0;">Thank you! Your review has been submitted.</div>
  <?php endif; ?>
  </div>
</div>

<script>
  // Star rating color change on hover & selection
  document.querySelectorAll('.star-rating label').forEach(label => {
    label.addEventListener('mouseenter', () => {
      highlightStars(label);
    });
    label.addEventListener('mouseleave', () => {
      resetStars();
    });
  });

  function highlightStars(label) {
    const allLabels = [...document.querySelectorAll('.star-rating label')];
    const index = allLabels.indexOf(label);
    allLabels.forEach((lab, i) => {
      lab.style.color = i <= index ? '#ff7e5f' : '#ccc';
    });
  }

  function resetStars() {
    const checked = document.querySelector('.star-rating input[type="radio"]:checked');
    const allLabels = [...document.querySelectorAll('.star-rating label')];
    if (checked) {
      const checkedIndex = allLabels.findIndex(lab => lab.htmlFor === checked.id);
      allLabels.forEach((lab, i) => {
        lab.style.color = i <= checkedIndex ? '#ff7e5f' : '#ccc';
      });
    } else {
      allLabels.forEach(lab => lab.style.color = '#ccc');
    }
  }

  // Update star colors on page load and after selection
  document.querySelectorAll('.star-rating input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', () => {
      resetStars();
    });
  });

  // Initialize star colors in case a rating is preselected
  resetStars();
  
</script>

  <script>
    // Updated AJAX function with better error handling
    function submitProduct(productId, shopId, type) {
      const btn = document.getElementById(`${type}-btn-${productId}`);
      const originalText = btn.innerHTML;

      // Show loading state
      btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Adding...`;
      btn.disabled = true;

      fetch('save_order.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          product_id: productId,
          shop_id: shopId,
          type: type
        })
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.status === 'success') {
          btn.innerHTML = `<i class="fas fa-check"></i> Added!`;
          btn.classList.add('added-to-cart');
        } else {
          throw new Error(data.message || 'Unknown error occurred');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert(`Failed to add to cart: ${error.message}`);
      });
    }

    // Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    document.querySelectorAll('.item-card').forEach(card => {
        const productName = card.querySelector('.item-name').textContent.toLowerCase();
        card.style.display = productName.includes(searchTerm) ? 'flex' : 'none';
    });
});
  </script>
</body>
</html>
