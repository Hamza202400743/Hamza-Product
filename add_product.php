<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name        = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price       = (float)($_POST['price'] ?? 0);
    $stock       = (int)($_POST['stock'] ?? 0);

    // Handle image upload (optional)
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadsDir = __DIR__ . '/uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0755, true);
        }

        $tmpName = $_FILES['image']['tmp_name'];
        $origName = basename($_FILES['image']['name']);
        $ext = pathinfo($origName, PATHINFO_EXTENSION);
        $base = pathinfo($origName, PATHINFO_FILENAME);
        $safeBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $base);
        $imageName = time() . '_' . $safeBase . ($ext ? '.' . $ext : '');
        move_uploaded_file($tmpName, $uploadsDir . '/' . $imageName);
    }

    // ensure products table has an `image` column; add if missing
    $colRes = $conn->query("SHOW COLUMNS FROM products LIKE 'image'");
    if ($colRes && $colRes->num_rows === 0) {
        // Best-effort: try to add the column (if permissions allow)
        $conn->query("ALTER TABLE products ADD COLUMN image VARCHAR(255) NULL");
    }

    if ($name !== '' && $category_id > 0) {
        // Build prepared statement depending on whether `image` column exists
        $hasImageCol = ($conn->query("SHOW COLUMNS FROM products LIKE 'image'") && $conn->query("SHOW COLUMNS FROM products LIKE 'image'")->num_rows > 0);

        if ($hasImageCol) {
            $stmt = $conn->prepare(
                "INSERT INTO products (product_name, category_id, price, stock, image) VALUES (?, ?, ?, ?, ?)"
            );
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sidis", $name, $category_id, $price, $stock, $imageName);
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO products (product_name, category_id, price, stock) VALUES (?, ?, ?, ?)"
            );
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sidi", $name, $category_id, $price, $stock);
        }

        $stmt->execute();

        header("Location: add_product.php?success=1");
        exit;
    }
}

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="add-product-page">
<div class="page-wrapper">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Add Product</h1>
            <a href="home.php" class="back-link">← Back to Dashboard</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <p class="success-message">Product added successfully!</p>
        <?php endif; ?>

        <div style="margin-bottom:14px;">
            <a href="view_products.php" class="btn-primary">
                View Products
            </a>
        </div>

        <form method="POST" enctype="multipart/form-data" class="form-vertical">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="" disabled selected>Select a category</option>
                    <?php while ($row = $categories->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['id']) ?>">
                            <?= htmlspecialchars($row['category_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" step="0.01" id="price" name="price" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" required>
                </div>
            </div>

            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                <div id="imagePreview" class="image-preview">
                    <img id="previewImg" src="" alt="Preview">
                </div>
            </div>

            <button type="submit" class="btn-primary">Add Product</button>
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