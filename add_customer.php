<?php
include 'database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');

    if ($full_name !== '') {
        $stmt = $conn->prepare(
            "INSERT INTO customers (full_name, email, phone, address)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $full_name, $email, $phone, $address);
        $stmt->execute();

        $customer_id = $stmt->insert_id;

        header("Location: add_customer.php?success=1&customer_id=" . $customer_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="customers-page">
<div class="page-wrapper">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Add Customer</h1>
            <a href="home.php" class="back-link">← Back to Dashboard</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
    <p class="success-message">
        Customer added successfully!
        <?php if (!empty($_GET['customer_id'])): ?>
            (ID: <?= htmlspecialchars($_GET['customer_id']) ?>)
        <?php endif; ?>
    </p>
<?php endif; ?>

<div style="margin-bottom:14px;">
    <a href="view_customers.php" class="btn-primary">
        View Customers
    </a>
</div>

        <form method="POST" class="form-vertical">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input id="full_name" name="full_name" type="text" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" type="text">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="2"></textarea>
            </div>

            <button type="submit" class="btn-primary">Add Customer</button>
        </form>
    </div>
</div>
</body>
</html>