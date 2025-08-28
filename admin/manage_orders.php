<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/config.php';

// Update order status
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['msg'] = "<p class='text-success'>Order #$order_id status updated to $status</p>";
    header("Location: manage_orders.php");
    exit;
}

// Fetch all orders with user details
$sql = "SELECT o.id, o.total_amount, o.status, o.created_at, 
               u.name AS customer_name, u.email AS customer_email
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="bg-white p-4 rounded shadow">
        <h2 class="mb-4">📦 Manage Orders</h2>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-info"><?= $_SESSION['msg']; unset($_SESSION['msg']); ?></div>
        <?php endif; ?>

        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Total Amount (₹)</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Items</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['customer_name']) ?></td>
                            <td><?= htmlspecialchars($row['customer_email']) ?></td>
                            <td><?= number_format($row['total_amount'], 2) ?></td>
                            <td>
                                <form method="post" class="d-flex">
                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                    <select name="status" class="form-select form-select-sm">
                                        <?php 
                                        $statuses = ['Pending','Processing','Shipped','Completed','Cancelled'];
                                        foreach ($statuses as $s): ?>
                                            <option value="<?= $s ?>" <?= $row['status'] == $s ? 'selected' : '' ?>><?= $s ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary ms-2">Update</button>
                                </form>
                            </td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <a href="view_order.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-secondary">View Items</a>
                            </td>
                            <td>
                                <form method="post" action="delete_order.php" onsubmit="return confirm('Delete this order?');">
                                    <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center">No orders found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="mt-4">
            <a href="admin_index.php" class="btn btn-outline-secondary">⬅ Back to Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>
