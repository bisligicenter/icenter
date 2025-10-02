<?php
require_once 'db.php';

$conn = getDBConnection();

// Fetch all categories
$sql = "SELECT category_id, category_name FROM categories ORDER BY category_name ASC";
$result = $conn->query($sql);

$categories = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'category_id' => $row['category_id'],
            'category_name' => $row['category_name']
        ];
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($categories);
?>
