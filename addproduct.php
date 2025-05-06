<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $conn = new mysqli("localhost", "root", "", "silk_aura");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Ensure shop_id is passed
    $shop_id = isset($_POST['shop_id']) ? $_POST['shop_id'] : 0;

    foreach ($_POST['item_name'] as $index => $item_name) {
        // Fetch other product details
        $quantity = $_POST['quantity'][$index] ?? '';
        $min_kgs = $_POST['min_kgs'][$index] ?? '';
        $price = $_POST['price'][$index] ?? 0;
        $description = $_POST['description'][$index] ?? '';

        // New specification fields
        $fabric = $_POST['fabric'][$index] ?? '';
        $spec_colour = $_POST['spec_colour'][$index] ?? '';
        $usage = $_POST['usage'][$index] ?? '';
        $patterns = $_POST['patterns'][$index] ?? '';
        $ply = $_POST['ply'][$index] ?? 0;
        $denier = $_POST['denier'][$index] ?? 0;

        // Image handling
        $upload_dir = "product_images/";
        $full_upload_path = __DIR__ . "/product_images/";

        // Ensure the folder exists
        if (!is_dir($full_upload_path)) {
            mkdir($full_upload_path, 0777, true);
        }

        $image_field_name = "product_images_{$index}";
        $image_paths = [];

        // Handle uploaded images
        if (isset($_FILES[$image_field_name])) {
            foreach ($_FILES[$image_field_name]['tmp_name'] as $key => $tmp_name) {
                $image_name = basename($_FILES[$image_field_name]['name'][$key]);
                $image_tmp = $_FILES[$image_field_name]['tmp_name'][$key];

                if (!empty($image_name)) {
                    $target_path = $full_upload_path . $image_name;
                    if (move_uploaded_file($image_tmp, $target_path)) {
                        $image_paths[] = $upload_dir . $image_name;
                    }
                }
            }
        }

        $image_paths_str = implode(',', $image_paths);

        // Insert product data into the database
        $stmt = $conn->prepare("INSERT INTO selleritem 
        (shop_id, item_name, quantity, min_kgs, price, description, image_paths, fabric, spec_colour, `usage`, patterns, ply, denier) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "issdsssssssii", 
            $shop_id,
            $item_name,
            $quantity,
            $min_kgs,
            $price,
            $description,
            $image_paths_str,
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
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    function addImageField(button, index) {
      const imageFields = button.previousElementSibling;
      const input = document.createElement('input');
      input.type = 'file';
      input.name = `product_images_${index}[]`;
      input.className = 'w-full px-4 py-2 border rounded bg-white';
      imageFields.appendChild(input);
    }

    function addProductBlock() {
      const container = document.getElementById('product-container');
      const index = container.children.length;

      const html = `
        <div class="product-block space-y-6 border border-gray-300 rounded-xl p-6">
          <div>
            <label class="block font-medium mb-1">Item Name</label>
            <select name="item_name[]" required class="w-full border px-4 py-2 rounded bg-white">
              <option value="">Select Item</option>
              <option value="Mulberry">Mulberry</option>
              <option value="Tussar">Tussar</option>
              <option value="Eri">Eri</option>
              <option value="Muga">Muga</option>
            </select>
          </div>
          <div><label class="block font-medium mb-1">Quantity</label><input type="number" name="quantity[]" required class="w-full border px-4 py-2 rounded" /></div>
          <div><label class="block font-medium mb-1">Minimum Kgs</label><input type="number" name="min_kgs[]" required class="w-full border px-4 py-2 rounded" /></div>
          <div><label class="block font-medium mb-1">Price</label><input type="number" name="price[]" required class="w-full border px-4 py-2 rounded" /></div>
          <div><label class="block font-medium mb-1">Description</label><textarea name="description[]" required class="w-full border px-4 py-2 rounded resize-none"></textarea></div>

          <details class="bg-pink-50 p-4 rounded-lg border border-pink-200">
            <summary class="font-semibold cursor-pointer text-pink-600">+ Product Specifications</summary>
            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div><label class="block font-medium mb-1">Fabric</label><input type="text" name="fabric[]" class="w-full border px-4 py-2 rounded" /></div>
              <div><label class="block font-medium mb-1">Colour</label><input type="text" name="spec_colour[]" class="w-full border px-4 py-2 rounded" /></div>
              <div><label class="block font-medium mb-1">Usage</label><input type="text" name="usage[]" class="w-full border px-4 py-2 rounded" /></div>
              <div><label class="block font-medium mb-1">Patterns</label><input type="text" name="patterns[]" class="w-full border px-4 py-2 rounded" /></div>
              <div><label class="block font-medium mb-1">No. of Ply</label><input type="number" name="ply[]" class="w-full border px-4 py-2 rounded" /></div>
              <div><label class="block font-medium mb-1">Denier</label><input type="number" name="denier[]" class="w-full border px-4 py-2 rounded" /></div>
            </div>
          </details>

          <div class="image-section">
            <label class="block font-medium mb-1 mt-4">Product Images</label>
            <div class="image-fields space-y-2">
              <input type="file" name="product_images_${index}[]" class="w-full px-4 py-2 border rounded bg-white" />
            </div>
            <button type="button" class="add-image-btn mt-2 text-sm text-pink-600 hover:underline" onclick="addImageField(this, ${index})">+ Add More Images</button>
          </div>
        </div>`;
      container.insertAdjacentHTML('beforeend', html);
    }

    window.onload = addProductBlock;
  </script>
</head>
<body class="bg-pink-50 py-10 px-4">
  <div class="text-center mb-8">
    <h1 class="text-4xl font-bold text-white py-4 rounded-xl" style="background: linear-gradient(90deg, #ff7e5f, #feb47b, #ff9a8b);">
      SILK AURO
    </h1>
  </div>

  <div class="max-w-5xl mx-auto bg-white p-8 rounded-xl shadow-xl">
    <h2 class="text-3xl font-bold text-pink-700 text-center mb-8">Add Silk Products</h2>

    <form action="" method="POST" enctype="multipart/form-data" id="product-form" class="space-y-6">
      <input type="hidden" name="shop_id" value="<?= htmlspecialchars($shop_id) ?>">
      <div id="product-container" class="space-y-6"></div>

      <div class="text-right">
        <button type="button" onclick="addProductBlock()" class="text-pink-700 hover:text-pink-900 font-semibold flex items-center text-sm">
          <span class="text-2xl mr-1">+</span> Add Product
        </button>
      </div>

      <div class="text-center pt-4">
        <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-6 rounded-lg">
          Submit Products
        </button>
      </div>
    </form>
  </div>
</body>
</html>
