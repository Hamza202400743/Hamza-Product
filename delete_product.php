<?php
include 'database.php';

if (!isset($_GET['id'])) {
    header("Location: view_products.php");
    exit;
}

$id = (int)$_GET['id'];

if ($id > 0) {
    // 1) delete related order_items
    $stmtItems = $conn->prepare("DELETE FROM order_items WHERE product_id = ?");
    if (!$stmtItems) {
        die("Prepare failed (order_items): " . $conn->error);
    }
    $stmtItems->bind_param("i", $id);
    $stmtItems->execute();

    // 2) delete product itself
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed (products): " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: view_products.php");
exit;