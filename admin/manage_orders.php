<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/config.php';

/* =========================
   Update Order Status
========================= */
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status   = $_POST['status'];

    // Check if order is already cancelled
    $check_stmt = $conn->prepare("SELECT status FROM orders WHERE id = ?");
    $check_stmt->bind_param("i", $order_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $current_order = $result->fetch_assoc();
    $check_stmt->close();

    if ($current_order && $current_order['status'] === 'Cancelled') {
        $_SESSION['msg'] = "Cannot change status of cancelled order #$order_id";
        header("Location: manage_orders.php");
        exit;
    }

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['msg'] = "Order #$order_id status updated";
    header("Location: manage_orders.php");
    exit;
}

/* =========================
   FETCH ORDERS (IMPORTANT)
========================= */
$sql = "SELECT o.id, o.total_amount, o.status, o.created_at,
               o.shipping_address,
               u.name, u.mobile
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Order query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #f3e5f5, #ede7f6);
}
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-Pending { background:#fff3cd; color:#856404; }
.status-Processing { background:#cce5ff; color:#084298; }
.status-Shipped { background:#e0ccff; color:#4a148c; }
.status-Completed { background:#d4edda; color:#155724; }
.status-Cancelled { background:#f8d7da; color:#721c24; }
</style>
</head>

<body>

<div class="container py-5">
<div class="bg-white p-4 rounded shadow">

<h3 class="mb-4">📦 Manage Orders</h3>

<?php if (isset($_SESSION['msg'])): ?>
<div class="alert alert-success">
<?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
</div>
<?php endif; ?>

<table class="table table-hover align-middle">
<thead class="table-dark">
<tr>
    <th>#</th>
    <th>Customer</th>
    <th>Mobile</th>
    <th>Total</th>
    <th>Status</th>
    <th>Date</th>
    <th>Address</th>
    <th>Items</th>
    <th>WhatsApp</th>
    <th>Delete</th>
</tr>
</thead>
<tbody>

<?php if ($result->num_rows > 0): ?>
<?php while ($row = $result->fetch_assoc()): ?>

<?php
// Item count
$item_q = $conn->query("SELECT COUNT(*) AS c FROM order_items WHERE order_id=".(int)$row['id']);
$item_count = $item_q->fetch_assoc()['c'];

// WhatsApp
$wa_number = preg_replace('/[^0-9]/', '', $row['mobile']);

$message  = "Hello {$row['name']}!\n\n";
$message .= " Order #{$row['id']} details:\n\n";

// fetch items
$item_sql = $conn->prepare("
    SELECT p.name, oi.quantity, oi.price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$item_sql->bind_param("i", $row['id']);
$item_sql->execute();
$item_res = $item_sql->get_result();

while ($item = $item_res->fetch_assoc()) {
    $message .= "- {$item['name']} (Qty: {$item['quantity']}) - SGD "
              . number_format($item['price'],2) . "\n";
}
$item_sql->close();

$message .= "\n Total: SGD ".number_format($row['total_amount'],2);
$message .= "\n\nThank you for shopping with XL Fashion Trends!";

$wa_link = "https://wa.me/$wa_number?text=".urlencode($message);
?>

<tr>
<td>#<?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['name']) ?></td>
<td><?= htmlspecialchars($row['mobile']) ?></td>
<td>SGD <?= number_format($row['total_amount'],2) ?></td>

<td>
<form method="post" class="d-flex gap-2 mb-1">
<input type="hidden" name="order_id" value="<?= $row['id'] ?>">
<select name="status" class="form-select form-select-sm" <?= $row['status'] === 'Cancelled' ? 'disabled' : '' ?>>
<?php foreach (['Pending','Processing','Shipped','Completed','Cancelled'] as $s): ?>
<option value="<?= $s ?>" <?= $row['status']===$s?'selected':'' ?>>
<?= $s ?>
</option>
<?php endforeach; ?>
</select>
<button name="update_status" class="btn btn-sm btn-primary" <?= $row['status'] === 'Cancelled' ? 'disabled' : '' ?>>Save</button>
</form>

<span class="status-badge status-<?= $row['status'] ?>">
<?= $row['status'] ?>
</span>
</td>

<td><?= date("d M Y, H:i", strtotime($row['created_at'])) ?></td>
<td style="max-width:220px;white-space:pre-wrap;">
<?= htmlspecialchars($row['shipping_address']) ?>
</td>

<td>
<a href="view_order.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary">
View (<?= $item_count ?>)
</a>
</td>

<td>
<a href="<?= $wa_link ?>" target="_blank" class="btn btn-sm btn-success">
📱 Send
</a>
</td>

<td>
<form method="post" action="delete_order.php"
onsubmit="return confirm('Delete order #<?= $row['id'] ?>?');">
<input type="hidden" name="id" value="<?= $row['id'] ?>">
<button class="btn btn-sm btn-danger">Delete</button>
</form>
</td>
</tr>

<?php endwhile; ?>
<?php else: ?>
<tr>
<td colspan="10" class="text-center text-muted py-4">
No orders found
</td>
</tr>
<?php endif; ?>

</tbody>
</table>

<div class="d-flex justify-content-between mt-4">
<a href="export_orders.php" class="btn btn-success">📥 Export Orders</a>
<a href="admin_index.php" class="btn btn-outline-secondary">⬅ Dashboard</a>
</div>

</div>
</div>

</body>
</html>
