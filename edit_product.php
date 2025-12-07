<?php
include 'database.php';

// 1. Validate and get ID
if (!isset($_GET['id'])) {
    header("Location: view_products.php");
    exit;
}

$id = (int)$_GET['id'];
if ($id <= 0) {
    header("Location: view_products.php");
    exit;
}

// Check if image column exists
$hasImageCol = false;
$colRes = $conn->query("SHOW COLUMNS FROM products LIKE 'image'");
if ($colRes && $colRes->num_rows > 0) {
    $hasImageCol = true;
}

// 2. If form submitted: update product
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name        = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price       = (float)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);

    // Handle image upload
    $imageName = $_POST['current_image'] ?? null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadsDir = __DIR__ . '/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        // Delete old image if exists
        if (!empty($imageName) && file_exists($uploadsDir . '/' . $imageName)) {
            unlink($uploadsDir . '/' . $imageName);
        }

        $tmpName = $_FILES['image']['tmp_name'];
        $origName = basename($_FILES['image']['name']);
        $ext = pathinfo($origName, PATHINFO_EXTENSION);
        $base = pathinfo($origName, PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $base);
        $imageName = time() . '_' . $safeBase . ($ext ? '.' . $ext : '');
        move_uploaded_file($tmpName, $uploadsDir . '/' . $imageName);
    }

    if ($name !== '' && $category_id > 0) {
        if ($hasImageCol) {
            $stmt = $conn->prepare(
                "UPDATE products
                 SET product_name = ?, category_id = ?, price = ?, stock = ?, image = ?
                 WHERE id = ?"
            );
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sidisi", $name, $category_id, $price, $stock, $imageName, $id);
        } else {
            $stmt = $conn->prepare(
                "UPDATE products
                 SET product_name = ?, category_id = ?, price = ?, stock = ?
                 WHERE id = ?"
            );
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sidii", $name, $category_id, $price, $stock, $id);
        }

        $stmt->execute();

        header("Location: view_products.php");
        exit;
    }
}

// 3. Load product data
$selectImage = $hasImageCol ? ", image" : "";
$stmt = $conn->prepare(
    "SELECT id, product_name, category_id, price, stock" . $selectImage . "
     FROM products
     WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "Product not found.";
    exit;
}

// 4. Load categories for dropdown
$categories = $conn->query("SELECT id, category_name FROM categories ORDER BY category_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="add-product-page">
<div class="page-wrapper">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Edit Product</h1>
            <a href="view_products.php" class="back-link">← Back to Products</a>
        </div>

        <form method="POST" enctype="multipart/form-data" class="form-vertical">
            <input type="hidden" name="current_image" value="<?= htmlspecialchars($product['image'] ?? '') ?>">
            
            <div class="form-group">
                <label for="name">Product Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($product['product_name']) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="" disabled>Select a category</option>
                    <?php while ($row = $categories->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['id']) ?>"
                            <?= $row['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['category_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input
                        type="number"
                        step="0.01"
                        id="price"
                        name="price"
                        value="<?= htmlspecialchars($product['price']) ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        value="<?= htmlspecialchars($product['stock']) ?>"
                        required
                    >
                </div>
            </div>

            <?php if ($hasImageCol): ?>
            <div class="form-group">
                <label for="image">Product Image</label>
                <?php if (!empty($product['image']) && file_exists(__DIR__ . '/uploads/' . $product['image'])): ?>
                    <div class="image-preview active" style="margin-bottom: 12px;">
                        <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="Current product image">
                        <p style="font-size: 12px; color: #6b7280; margin-top: 8px;">Current image - upload new to replace</p>
                    </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                <div id="imagePreview" class="image-preview">
                    <img id="previewImg" src="" alt="Preview">
                    <p style="font-size: 12px; color: #6b7280; margin-top: 8px;">New image preview</p>
                </div>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.add('active');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.remove('active');
    }
}
</script>
</body>
</html>