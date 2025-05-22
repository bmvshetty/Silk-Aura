<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "silk_aura");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
  

    $shop_id = isset($_POST['shop_id']) ? $_POST['shop_id'] : 0;


    foreach ($_POST['item_name'] as $index => $item_name) {
        $quantity = $_POST['quantity'][$index] ?? '';
        $price = $_POST['price'][$index] ?? 0;
        $description = $_POST['description'][$index] ?? '';
        $fabric = $_POST['fabric'][$index] ?? '';
        $spec_colour = $_POST['spec_colour'][$index] ?? '';
        $usage = $_POST['usage'][$index] ?? '';
        $patterns = $_POST['patterns'][$index] ?? '';
        $ply = $_POST['ply'][$index] ?? 0;
        $denier = $_POST['denier'][$index] ?? 0;

        // Handle image upload and read binary
        $image_field_name = "product_images_{$index}";
        $imageData = null;

         if (isset($_FILES[$image_field_name])) {
            foreach ($_FILES[$image_field_name]['tmp_name'] as $key => $tmp_name) {
                if (is_uploaded_file($tmp_name)) {
                    $imageData = file_get_contents($tmp_name);
                    break; 
                }
            }
          }
        // Insert product data into the database
        
        $stmt = $conn->prepare("INSERT INTO selleritem 
            (shop_id, item_name, quantity, price, description, image_paths, fabric, spec_colour, `usage`, patterns, ply, denier) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "issdsssssisi",
            $shop_id,
            $item_name,
            $quantity,
            $price,
            $description,
            $imageData, // placeholder for image BLOB
            $fabric,
            $spec_colour,
            $usage,
            $patterns,
            $ply,
            $denier
        );

        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

        if (isset($_FILES[$image_field_name])) {
    foreach ($_FILES[$image_field_name]['tmp_name'] as $key => $tmp_name) {
        if (is_uploaded_file($tmp_name)) {
            $imageData = file_get_contents($tmp_name);
            if (!$imageData) {
                echo "Image data not loaded.";
            }
            break;
        }
    }
}

        $stmt->close();
    }

    $conn->close();
    header("Location: shopsuccess.php?shop_id={$shop_id}");
    exit;
}
?>
<?php $shop_id = $_GET['shop_id'] ?? 0; ?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Silk Products | Silk Auro</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      background-color: #fce7ef;
      font-family: Arial, sans-serif;
      padding: 20px;
    }
    h1, h2 {
      text-align: center;
    }
    .header {
      background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);
      color: white;
      padding: 20px;
      border-radius: 10px;
      font-size: 2em;
      margin-bottom: 20px;
      text-align: center;
    }
    .container {
      max-width: 900px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .product-block {
      border: 1px solid #ccc;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
    }
    label {
      font-weight: bold;
      display: block;
      margin: 10px 0 5px;
    }
    input[type="text"],
    input[type="number"],
    input[type="file"],
    textarea,
    select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    textarea {
      resize: none;
    }
    .add-btn,
    .submit-btn {
      padding: 10px 20px;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .add-btn {
      background-color: #ff7e5f;
      color: white;
    }
    .submit-btn {
      background-color: #d6336c;
      color: white;
    }
    details {
      background-color: #ffe4ed;
      padding: 15px;
      border: 1px solid #f7a8bc;
      border-radius: 8px;
    }
    .image-fields input {
      margin-bottom: 10px;
    }
    .add-image-btn {
      color: #d6336c;
      cursor: pointer;
      font-size: 0.9em;
    }
  </style>
  <script>
    function addImageField(button, index) {
      const imageFields = button.previousElementSibling;
      const input = document.createElement('input');
      input.type = 'file';
      input.name = `product_images_${index}[]`;
      imageFields.appendChild(input);
    }

    function addProductBlock() {
      const container = document.getElementById('product-container');
      const index = container.children.length;

      const html = `  
        <div class="product-block">
          <label>Item Name</label>
          <input type="text" name="item_name[]" required />

          <label>Quantity</label>
          <input type="number" name="quantity[]" required />

          <label>Price</label>
          <input type="number" name="price[]" required />

          <label>Description</label>
          <textarea name="description[]" required></textarea>

          <details>
            <summary>+ Product Specifications</summary>
            <label>Fabric</label>
            <input type="text" name="fabric[]" />
            <label>Colour</label>
            <input type="text" name="spec_colour[]" />
            <label>Usage</label>
            <input type="text" name="usage[]" />
            <label>Patterns</label>
            <input type="text" name="patterns[]" />
            <label>No. of Ply</label>
            <input type="number" name="ply[]" />
            <label>Denier</label>
            <input type="number" name="denier[]" />
          </details>

          <label>Product Images</label>
          <div class="image-fields">
          <input type="file" name="product_images_${index}[]" />

      `;

      container.insertAdjacentHTML('beforeend', html);
    }

    window.onload = addProductBlock;
  </script>
</head>
<body>
  <div class="header">SILK AURO</div>

  <div class="container">
    <h2>Add Silk Products</h2>

    <form action="" method="POST" enctype="multipart/form-data" id="product-form">
      <input type="hidden" name="shop_id" value="<?= htmlspecialchars($shop_id) ?>">
      <div id="product-container"></div>

      <div style="text-align: center; margin-top: 20px;">
        <button type="submit" class="submit-btn">Submit Products</button>
      </div>
    </form>
  </div>
</body>
</html>