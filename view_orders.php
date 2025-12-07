<?php
include 'database.php';

$sql = "SELECT o.id,
               o.order_date,
               c.full_name AS customer_name
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        ORDER BY o.id DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="orders-page">
<div class="page-wrapper">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Orders</h1>
            <a href="home.php" class="back-link">← Back to Dashboard</a>
        </div>

        <table class="orders-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Order Date</th>
                <th>Customer</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['order_date']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr class="empty-row">
                    <td colspan="3">No orders found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>