<?php
include 'database.php';

$sql = "SELECT id, full_name, email, phone, address, date_joined
        FROM customers
        ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="customers-page">
<div class="page-wrapper">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Customers</h1>
            <a href="home.php" class="back-link">← Back to Dashboard</a>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Date Joined</th>
                <th style="width:110px;">Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= htmlspecialchars($row['date_joined']) ?></td>
                        <td>
                            <a href="delete_customer.php?id=<?= $row['id'] ?>"
                               class="btn-outline"
                               style="font-size:12px;padding:4px 10px;"
                               onclick="return confirm('Delete this customer?');">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr class="empty-row">
                    <td colspan="7">No customers found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>