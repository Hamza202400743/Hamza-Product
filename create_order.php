<?php
include 'database.php';

$customers = $conn->query("SELECT id, full_name FROM customers ORDER BY full_name");
$products  = $conn->query("SELECT id, product_name FROM products ORDER BY product_name");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $product_id  = (int)($_POST['product_id'] ?? 0);
    $quantity    = (int)($_POST['quantity'] ?? 1);

    if ($customer_id > 0 && $product_id > 0 && $quantity > 0) {
        // Create order with 0 total_amount for now
        $stmt = $conn->prepare(
            "INSERT INTO orders (customer_id, total_amount) VALUES (?, 0)"
        );
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        $stmt2 = $conn->prepare(
            "INSERT INTO order_items (order_id, product_id, quantity)
             VALUES (?, ?, ?)"
        );
        $stmt2->bind_param("iii", $order_id, $product_id, $quantity);
        $stmt2->execute();

        // Redirect back with success message and order id
        header("Location: create_order.php?success=1&order_id=" . $order_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Order</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="orders-create-page">
<div class="page-wrapper">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Create Order</h1>
            <a href="home.php" class="back-link">← Back to Dashboard</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
    <p class="success-message">
        Order created successfully!
        <?php if (!empty($_GET['order_id'])): ?>
            (ID: <?= htmlspecialchars($_GET['order_id']) ?>)
        <?php endif; ?>
    </p>
<?php endif; ?>

<a href="view_orders.php" class="btn-primary" style="margin-bottom:14px; display:inline-flex;">
    View Orders
</a>

        <form method="POST" class="form-vertical">
            <div class="form-group">
                <label for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="" disabled selected>Select Customer</option>
                    <?php while ($c = $customers->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($c['id']) ?>">
                            <?= htmlspecialchars($c['full_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="" disabled selected>Select Product</option>
                    <?php while ($p = $products->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($p['id']) ?>">
                            <?= htmlspecialchars($p['product_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="number" min="1" value="1" required>
            </div>

            <button type="submit" class="btn-primary">Create Order</button>
        </form>
    </div>
</div>
</body>
</html>