<?php
session_start();
include 'includes/config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    $_SESSION['error'] = "Invalid Order ID.";
    header("Location: my_orders.php");
    exit;
}

// 1. Verify the order belongs to the user and is 'Pending'
$stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$order = $res->fetch_assoc();
$stmt->close();

if (!$order) {
    $_SESSION['error'] = "Order not found or access denied.";
    header("Location: my_orders.php");
    exit;
}

if ($order['status'] !== 'Pending') {
    $_SESSION['error'] = "Only 'Pending' orders can be cancelled.";
    header("Location: my_orders.php");
    exit;
}

// 2. Start transaction for safety
$conn->begin_transaction();

try {
    // 3. Update order status
    $upd = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
    $upd->bind_param("i", $order_id);
    $upd->execute();
    $upd->close();

    // 4. Restore Stock
    $items_stmt = $conn->prepare("SELECT product_id, quantity, size FROM order_items WHERE order_id = ?");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();

    while ($item = $items_result->fetch_assoc()) {
        $pid = $item['product_id'];
        $qty = $item['quantity'];
        $size = $item['size'];

        // Get current stock
        $p = $conn->query("SELECT size_stock, stock FROM products WHERE id = $pid FOR UPDATE");
        $pData = $p->fetch_assoc();
        $size_stock_array = json_decode($pData['size_stock'], true) ?: [];
        $current_db_stock = intval($pData['stock']);

        if ($size !== "" && $size !== "N/A" && $size !== "NOSIZE") {
            // Product with multiple sizes
            if (isset($size_stock_array[$size])) {
                $size_stock_array[$size] = intval($size_stock_array[$size]) + $qty;
            }

            // Recalculate total stock
            $new_total_stock = 0;
            foreach ($size_stock_array as $s => $st) {
                $new_total_stock += intval($st);
            }

            $updated_json = json_encode($size_stock_array);
            $conn->query("UPDATE products SET size_stock = '$updated_json', stock = $new_total_stock WHERE id = $pid");
        } else {
            // No size products
            $new_stock = $current_db_stock + $qty;
            $size_stock_array['stock'] = $new_stock;
            $updated_json = json_encode($size_stock_array);
            $conn->query("UPDATE products SET size_stock = '$updated_json', stock = $new_stock WHERE id = $pid");
        }
    }
    $items_stmt->close();

    $conn->commit();
    $_SESSION['success'] = "Order #$order_id has been cancelled and stock has been restored.";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Something went wrong! Order could not be cancelled.";
}

header("Location: my_orders.php");
exit;
?>
