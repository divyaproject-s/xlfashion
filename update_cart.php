<?php
session_start();
include 'includes/config.php';

// Ensure cart exists
if (!isset($_SESSION['cart']))
    $_SESSION['cart'] = [];

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $key = $_POST['key'] ?? '';
    $qty = (int) ($_POST['qty'] ?? 1);

    if ($key && isset($_SESSION['cart'][$key])) {

        $item = &$_SESSION['cart'][$key];

        // Get latest stock from DB
        $stmt = $conn->prepare("SELECT size_stock, stock FROM products WHERE id=?");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $size_stock = json_decode($result['size_stock'], true);

        // Determine available stock
        if ($item['size'] !== 'NOSIZE' && $item['size'] !== 'N/A') {
            // SIZE BASED
            $available_stock = intval($size_stock[$item['size']] ?? 0);
        } else {
            // NO SIZE ITEMS (bags, saree, home decors)
            // Use stock column
            $available_stock = intval($result['stock']);
        }

        // IMPORTANT FIX: update size_stock inside SESSION
        $item['size_stock'] = $size_stock;
        $item['stock'] = $available_stock;

        // Cap quantity to available stock
        if ($qty > $available_stock) {
            $qty = $available_stock;
        }

        if ($qty > 0) {
            $item['qty'] = $qty;

            echo json_encode([
                'status' => 'success',
                'qty' => $qty,
                'total' => $qty * $item['price'],
                'stock' => $available_stock
            ]);
        } else {
            unset($_SESSION['cart'][$key]);
            echo json_encode(['status' => 'removed']);
        }

    } else {
        echo json_encode(['status' => 'error', 'message' => 'Item not found in cart']);
    }

    exit;
}

// Handle GET remove/clear
$action = $_GET['action'] ?? '';
$key = $_GET['key'] ?? '';

if ($action === 'remove' && $key) {
    unset($_SESSION['cart'][$key]);
    echo json_encode(['status' => 'removed']);
    exit;
} elseif ($action === 'clear') {
    $_SESSION['cart'] = [];
    echo json_encode(['status' => 'cleared']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
