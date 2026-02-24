<?php
session_start();
require_once __DIR__ . '/includes/config.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login or signup to add items to your cart.',
        'login_redirect' => 'login.php',
        'signup_redirect' => 'signup.php'
    ]);
    exit();
}

$user_id = $_SESSION['user_id'];

$product_id = $_POST['product_id'] ?? null;
$quantity = intval($_POST['quantity'] ?? 1);
$size = $_POST['size'] ?? '';

// 🔥 FIX — Convert empty size to NOSIZE
if ($size === '' || $size === null) {
    $size = 'NOSIZE';
}

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit();
}

// Fetch product stock
$stmt = $conn->prepare("SELECT size_stock, stock FROM products WHERE id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found.']);
    exit();
}

$stockData = json_decode($product['size_stock'], true);

// Get available stock
if ($size === 'NOSIZE') {
    $available_stock = intval($product['stock']);
} else {
    $available_stock = intval($stockData[$size] ?? 0);
}

// If no stock
if ($available_stock <= 0) {
    echo json_encode(['success' => false, 'message' => 'Product out of stock.']);
    exit();
}

// Check existing cart quantity
$stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id=? AND product_id=? AND size=?");
$stmt->bind_param("iis", $user_id, $product_id, $size);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();
$stmt->close();

$current_qty = $existing['quantity'] ?? 0;

// Prevent exceeding stock
if ($current_qty + $quantity > $available_stock) {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot add more than available stock!'
    ]);
    exit();
}

// Insert or update cart
$stmt = $conn->prepare("
    INSERT INTO cart (user_id, product_id, size, quantity)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
");
$stmt->bind_param("iisi", $user_id, $product_id, $size, $quantity);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true, 'message' => 'Product added to cart.']);
exit();
?>