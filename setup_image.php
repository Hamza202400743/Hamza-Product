<?php
include 'database.php';

// Add image column
$sql = "ALTER TABLE products ADD COLUMN image VARCHAR(255) NULL AFTER stock";

if ($conn->query($sql) === TRUE) {
    echo "✅ Image column added successfully!";
} else {
    if (strpos($conn->error, 'Duplicate column') !== false) {
        echo "ℹ️ Image column already exists!";
    } else {
        echo "❌ Error: " . $conn->error;
    }
}

$conn->close();
?>