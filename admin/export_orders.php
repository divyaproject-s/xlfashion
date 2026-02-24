<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/config.php';

// Fetch all orders with user info
$sql = "SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, o.shipping_address,
               u.name, u.mobile
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);

// Set headers to force download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_report_' . date('Y-m-d') . '.csv');

// Output CSV column headers
$output = fopen('php://output', 'w');
fputcsv($output, ['Order ID','Customer Name','Mobile','Total Amount','Status','Date','Shipping Address','Items']);

// Loop through orders
while ($row = $result->fetch_assoc()) {

    // Fetch order items as a single string
    $items_sql = $conn->prepare("
        SELECT p.name, oi.quantity, oi.price, oi.size
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $items_sql->bind_param("i", $row['id']);
    $items_sql->execute();
    $items_result = $items_sql->get_result();

    $items_text = '';
    while ($item = $items_result->fetch_assoc()) {
        $items_text .= $item['name'] . ' (Qty: ' . $item['quantity'] . ', Size: ' . ($item['size'] ?? 'N/A') . ', Price: ' . $item['price'] . ') | ';
    }
    $items_text = rtrim($items_text, ' | ');

    fputcsv($output, [
        $row['id'],
        $row['name'],
        $row['mobile'],
        $row['total_amount'],
        $row['status'],
        $row['created_at'],
        $row['shipping_address'],
        $items_text
    ]);
}

fclose($output);
exit;
?>
