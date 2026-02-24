<?php
header('Content-Type: application/json');
session_start();
include 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to use the wishlist.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if ($product_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product.']);
    exit;
}

// Check if already in wishlist
$check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Already exists - Remove it
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'removed', 'message' => 'Removed from wishlist.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to remove from wishlist.']);
    }
} else {
    // Doesn't exist - Add it
    $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $product_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'added', 'message' => 'Added to wishlist.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add to wishlist.']);
    }
}
exit;
