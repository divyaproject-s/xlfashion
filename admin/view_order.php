<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/config.php';

$order_id = intval($_GET['id'] ?? 0);

$sql = "SELECT oi.quantity, oi.price, p.name, p.image 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Items</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="bg-white p-4 rounded shadow">
        <h2>🛒 Order #<?= $order_id ?> Items</h2>

        <table class="table table-striped mt-3 align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price (₹)</th>
                    <th>Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php $grand_total = 0; ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php $total = $row['quantity'] * $row['price']; ?>
                    <?php $grand_total += $total; ?>
                    <tr>
                        <td>
                            <?php if (!empty($row['image'])): ?>
                                <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" class="product-img" alt="<?= htmlspecialchars($row['name']) ?>">
                            <?php else: ?>
                                <span class="text-muted">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= number_format($row['price'], 2) ?></td>
                        <td><?= number_format($total, 2) ?></td>
                    </tr>
                <?php endwhile; ?>
                <tr class="table-secondary">
                    <td colspan="4"><strong>Grand Total</strong></td>
                    <td><strong><?= number_format($grand_total, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>

        <a href="manage_orders.php" class="btn btn-outline-secondary">⬅ Back to Orders</a>
    </div>
</div>
</body>
</html>
