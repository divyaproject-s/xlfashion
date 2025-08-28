<?php
session_start();
require_once __DIR__ . '/includes/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to submit a review.']);
    exit();
}

// Get POST data
$product_id = $_POST['product_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$comment = trim($_POST['comment'] ?? '');
$user_id = $_SESSION['user_id'];

// Validate input
if (!$product_id || !is_numeric($product_id)) {
    echo json_encode(['success' => false, 'message' => 'Product ID not provided or invalid.']);
    exit();
}

if (!$rating || !is_numeric($rating) || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating. Please provide a rating between 1 and 5.']);
    exit();
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
    exit();
}

// Check if user has already reviewed this product
$stmt_check = $conn->prepare("SELECT id FROM product_reviews WHERE product_id = ? AND user_id = ?");
$stmt_check->bind_param("ii", $product_id, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // User already reviewed, update existing review
    $stmt = $conn->prepare("UPDATE product_reviews SET rating = ?, comment = ? WHERE product_id = ? AND user_id = ?");
    $stmt->bind_param("isii", $rating, $comment, $product_id, $user_id);
    $action_message = 'Review updated successfully.';
} else {
    // Insert new review
    $stmt = $conn->prepare("INSERT INTO product_reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);
    $action_message = 'Review submitted successfully.';
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => $action_message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit review: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
