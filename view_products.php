<?php
include 'database.php';

// detect if `image` column exists so we can optionally include it
$hasImageCol = false;
$colRes = $conn->query("SHOW COLUMNS FROM products LIKE 'image'");
if ($colRes && $colRes->num_rows > 0) {
    $hasImageCol = true;
}

$selectImage = $hasImageCol ? ", p.image" : "";
$sql = "SELECT p.id, p.product_name, p.price, p.stock" . $selectImage . ", c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC";
$res = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="products-page">
<div class="page-wrapper">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Products</h1>
            <div style="display: flex; gap: 12px; align-items: center;">
                <a href="add_product.php" class="btn-primary" style="font-size: 14px; padding: 8px 16px;">
                    + Add Product
                </a>
                <a href="home.php" class="back-link">← Back to Dashboard</a>
            </div>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th style="width:150px;">Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($res && $res->num_rows > 0): ?>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td>
                            <div class="product-cell">
                                <?php if ($hasImageCol && !empty($row['image']) && file_exists(__DIR__ . '/uploads/' . $row['image'])): ?>
                                    <img src="uploads/<?= htmlspecialchars($row['image']) ?>" 
                                         alt="<?= htmlspecialchars($row['product_name']) ?>" 
                                         class="product-thumb">
                                <?php else: ?>
                                    <div class="product-thumb-placeholder">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                            <polyline points="21 15 16 10 5 21"></polyline>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <span><?= htmlspecialchars($row['product_name']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td>₱<?= number_format($row['price'], 2) ?></td>
                        <td><?= htmlspecialchars($row['stock']) ?></td>
                        <td>
                            <a href="edit_product.php?id=<?= $row['id'] ?>"
                               class="btn-primary"
                               style="font-size:12px;padding:4px 10px;margin-right:6px;">
                                Edit
                            </a>

                            <a href="delete_product.php?id=<?= $row['id'] ?>"
                               class="btn-outline"
                               style="font-size:12px;padding:4px 10px;"
                               onclick="return confirm('Delete this product?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr class="empty-row">
                    <td colspan="6">No products found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>