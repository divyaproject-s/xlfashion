<?php
session_start();
include 'includes/config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];

if (isset($_POST['place_order'])) {

    if (empty($cart)) {
        $_SESSION['error'] = "Your cart is empty!";
        header("Location: checkout.php");
        exit;
    }

    // --- Handle Shipping Address ---
    $shipping_address = "";
    $address_option = $_POST['address_option'] ?? "default";

    if ($address_option === "new" && !empty(trim($_POST['new_address']))) {
        $shipping_address = mysqli_real_escape_string($conn, trim($_POST['new_address']));
    } else {
        $res = $conn->query("SELECT address FROM users WHERE id=$user_id LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            $shipping_address = $row['address'] ?: "No address provided";
        } else {
            $shipping_address = "No address provided";
        }
    }

    // --- Calculate Grand Total ---
    $grand_total = 0;
    foreach ($cart as $item) {
        $qty = (int) ($item['qty'] ?? 1);
        if ($qty < 1)
            $qty = 1;
        $price = (float) ($item['price'] ?? 0);
        $grand_total += $qty * $price;
    }

    // --- Insert Order ---
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address, created_at) VALUES (?, ?, 'Pending', ?, NOW())");
    $stmt->bind_param("ids", $user_id, $grand_total, $shipping_address);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // ================================
    // INSERT ORDER ITEMS + UPDATE STOCK
    // ================================

    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)");

    foreach ($cart as $item) {

        $product_id = (int) $item['id'];
        $qty = (int) $item['qty'];
        if ($qty < 1)
            $qty = 1;
        $price = (float) $item['price'];
        $size = $item['size'] ?? '';

        // --- 1. Insert into order_items ---
        $stmt->bind_param("iiids", $order_id, $product_id, $qty, $price, $size);
        $stmt->execute();

        // --- 2. Get current stock from DB ---
        $p = $conn->query("SELECT size_stock, stock FROM products WHERE id=$product_id");
        $pData = $p->fetch_assoc();
        $size_stock_array = json_decode($pData['size_stock'], true) ?: [];
        $current_db_stock = intval($pData['stock']);

        // --- 3. Reduce stock correctly ---
        if ($size !== "" && $size !== "N/A" && $size !== "NOSIZE") {
            // Product with multiple sizes
            if (isset($size_stock_array[$size])) {
                $size_stock_array[$size] = max(0, intval($size_stock_array[$size]) - $qty);
            }

            // Recalculate total stock
            $new_total_stock = 0;
            foreach ($size_stock_array as $s => $st) {
                $new_total_stock += intval($st);
            }

            // Update DB
            $updated_json = json_encode($size_stock_array);
            $conn->query("UPDATE products SET size_stock='$updated_json', stock=$new_total_stock WHERE id=$product_id");

        } else {
            // Products like Saree (no size) or NOSIZE
            // Use stock column as truth
            $new_stock = max(0, $current_db_stock - $qty);

            // Sync size_stock
            $size_stock_array['stock'] = $new_stock;
            if (isset($size_stock_array['total']))
                unset($size_stock_array['total']); // Clean up legacy

            $updated_json = json_encode($size_stock_array);
            $conn->query("UPDATE products SET size_stock='$updated_json', stock=$new_stock WHERE id=$product_id");
        }

    }

    $stmt->close();

    // --- Clear Cart ---
    unset($_SESSION['cart']);

    // --- Success Message and Redirect ---
    $_SESSION['success'] = "Your order #$order_id has been placed successfully!";
    header("Location: order_success.php?id=" . $order_id);
    exit;
}

// If accessed directly, redirect to checkout
header("Location: checkout.php");
exit;
?>