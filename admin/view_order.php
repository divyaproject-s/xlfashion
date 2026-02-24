<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/config.php';

/* ===========================
   1. Validate Order ID
=========================== */
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    die("Invalid Order ID");
}

/* ===========================
   2. Fetch Order + Customer
=========================== */
$sql = "SELECT o.id, o.total_amount, o.status, o.created_at, o.shipping_address,
               u.name AS customer_name, u.mobile
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();
$order = $order_result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found");
}

/* ===========================
   3. Fetch Order Items
=========================== */
$sql = "SELECT oi.quantity, oi.price, oi.size,
               p.name, p.image, p.image_url
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order #<?= (int)$order_id ?> | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg, #f3e5f5, #ede7f6);
    font-family: 'Segoe UI', sans-serif;
}
.container {
    max-width: 1100px;
    margin-top: 40px;
}
.card {
    border-radius: 18px;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.card-header {
    background: linear-gradient(135deg, #6a1b9a, #8e24aa);
    color: #fff;
    border-radius: 18px 18px 0 0;
}
.info-box {
    background: #fafafa;
    border-radius: 14px;
    padding: 18px;
    height: 100%;
}
.info-box h6 {
    color: #6a1b9a;
    font-weight: 600;
}
.product-img {
    width: 65px;
    height: 65px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #ddd;
}
.table thead {
    background: #6a1b9a;
    color: #fff;
}
.table tbody tr:hover {
    background: #f3e5f5;
}
.total-row {
    background: #ede7f6;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="container">

<div class="card">

<!-- HEADER -->
<div class="card-header d-flex justify-content-between align-items-center">
    <div>
        <h4 class="mb-1">Order #<?= (int)$order_id ?></h4>
        <small>Placed on <?= htmlspecialchars($order['created_at']) ?></small>
    </div>
    <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
        <?= htmlspecialchars($order['status']) ?>
    </span>
</div>

<div class="card-body">

<!-- INFO -->
<div class="row g-4 mb-4">

<div class="col-md-6">
<div class="info-box">
<h6><i class="bi bi-person-circle me-2"></i>Customer Details</h6>
<p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
<p><strong>Mobile:</strong> <?= htmlspecialchars($order['mobile']) ?></p>
<p><strong>Address:</strong><br>
<?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
</p>
</div>
</div>

<div class="col-md-6">
<div class="info-box">
<h6><i class="bi bi-receipt me-2"></i>Order Summary</h6>
<p><strong>Order ID:</strong> #<?= (int)$order_id ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
<p><strong>Total:</strong> ₹<?= number_format($order['total_amount'], 2) ?></p>
</div>
</div>

</div>

<!-- ITEMS -->
<h5 class="mb-3"><i class="bi bi-cart-check me-2"></i>Ordered Items</h5>

<div class="table-responsive">
<table class="table align-middle">
<thead>
<tr>
    <th>Product</th>
    <th>Details</th>
    <th class="text-center">Qty</th>
    <th class="text-end">Price</th>
    <th class="text-end">Total</th>
</tr>
</thead>
<tbody>

<?php
$grand_total = 0;

if ($result && $result->num_rows > 0):
while ($row = $result->fetch_assoc()):
$total = $row['quantity'] * $row['price'];
$grand_total += $total;
?>
<tr>
<td>
<?php 
$p_img = $row['image'];
$p_url = $row['image_url'] ?? '';
$p_src = !empty($p_url) ? $p_url : (!empty($p_img) ? "../assets/images/" . $p_img : "");
if (!empty($p_src)): 
?>
<img src="<?= htmlspecialchars($p_src) ?>" class="product-img">
<?php else: ?>
<span class="text-muted">No Image</span>
<?php endif; ?>
</td>

<td>
<strong><?= htmlspecialchars($row['name']) ?></strong><br>
<small class="text-muted">Size: <?= htmlspecialchars($row['size'] ?? 'N/A') ?></small>
</td>

<td class="text-center"><?= (int)$row['quantity'] ?></td>
<td class="text-end">₹<?= number_format($row['price'], 2) ?></td>
<td class="text-end">₹<?= number_format($total, 2) ?></td>
</tr>

<?php endwhile; ?>
<tr class="total-row">
<td colspan="4" class="text-end">Grand Total</td>
<td class="text-end">₹<?= number_format($grand_total, 2) ?></td>
</tr>
<?php else: ?>
<tr>
<td colspan="5" class="text-center text-muted">No items found</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div>

<!-- ACTIONS -->
<div class="d-flex justify-content-between mt-4">
<a href="manage_orders.php" class="btn btn-outline-secondary">
<i class="bi bi-arrow-left"></i> Back
</a>

<a href="print_bill.php?id=<?= (int)$order_id ?>" target="_blank" class="btn btn-primary">
<i class="bi bi-printer"></i> Print Invoice
</a>
</div>

</div>
</div>
</div>

</body>
</html>
