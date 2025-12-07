<?php
include 'database.php';

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        header("Location: manage_categories.php");
        exit;
    }
}

$result = $conn->query("SELECT * FROM categories ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="categories-page">
<div class="page-wrapper">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Manage Categories</h1>
            <a href="home.php" class="back-link">← Back to Dashboard</a>
        </div>

        <form method="POST" class="form-vertical" style="margin-bottom:16px;">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group" style="align-self:flex-end;">
                    <button type="submit" class="btn-primary">Add</button>
                </div>
            </div>
        </form>

        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th style="width:110px;">Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td>
                            <a href="delete_category.php?id=<?= $row['id'] ?>"
                               class="btn-outline"
                               style="font-size:12px;padding:4px 10px;"
                               onclick="return confirm('Delete this category?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr class="empty-row">
                    <td colspan="3">No categories found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>