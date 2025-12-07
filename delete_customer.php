<?php
include 'database.php';

if (!isset($_GET['id'])) {
    header("Location: view_customers.php");
    exit;
}

$id = (int)$_GET['id'];
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

header("Location: view_customers.php");
exit;