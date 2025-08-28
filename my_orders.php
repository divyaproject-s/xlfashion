<?php
include 'includes/config.php';
include 'includes/header.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If not logged in → redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders for this user
$sql_orders = "
    SELECT orders.id, orders.total_amount, orders.status, orders.created_at
    FROM orders
    WHERE orders.user_id = $user_id
    ORDER BY orders.created_at DESC
";
$result_orders = $conn->query($sql_orders);
?>

<div class="container my-5">
    <h2 class="mb-4 fw-bold text-center">🛒 My Orders</h2>

    <?php if ($result_orders->num_rows > 0): ?>
        <?php while ($order = $result_orders->fetch_assoc()): ?>
            <div class="card shadow mb-4 order-card">
                <div class="card-header text-white fw-semibold d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);">
                    <span>Order #<?= $order['id'] ?></span>
                    <span class="small"><?= date("d M Y, H:i", strtotime($order['created_at'])) ?></span>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Status:</strong> 
                        <span class="badge bg-<?php 
                            echo ($order['status'] == 'Completed') ? 'success' : 
                                 (($order['status'] == 'Pending') ? 'warning text-dark' : 'secondary'); 
                        ?>">
                            <?= htmlspecialchars($order['status']) ?>
                        </span>
                    </p>
                    <p class="mb-3"><strong>Total:</strong> ₹<?= number_format($order['total_amount'], 2) ?></p>

                    <!-- Order Items -->
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th width="100">Qty</th>
                                    <th width="120">Price (₹)</th>
                                    <th width="140">Subtotal (₹)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $order_id = $order['id'];
                                $items_result = $conn->query("
                                    SELECT p.name, p.image, oi.quantity, oi.price
                                    FROM order_items oi
                                    JOIN products p ON oi.product_id = p.id
                                    WHERE oi.order_id = $order_id
                                ");
                                while ($item = $items_result->fetch_assoc()):
                                    $subtotal = $item['quantity'] * $item['price'];
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>" 
                                                     width="60" height="60" 
                                                     class="me-2 rounded shadow-sm border">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </div>
                                        </td>
                                        <td><?= (int)$item['quantity'] ?></td>
                                        <td><?= number_format($item['price'], 2) ?></td>
                                        <td><strong><?= number_format($subtotal, 2) ?></strong></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">
            You have not placed any orders yet.  
            <a href="products.php" class="btn btn-sm btn-gradient ms-2">Shop Now</a>
        </div>
    <?php endif; ?>
</div>

<!-- Gradient Button Style -->
<style>
    .btn-gradient {
        background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
        color: #fff !important;
        border-radius: 40px;
        padding: 6px 18px;
        font-weight: 600;
        text-decoration: none;
        transition: all .2s ease;
    }
    .btn-gradient:hover {
        box-shadow: 0 4px 12px rgba(124,14,32,.3);
        transform: translateY(-2px);
    }
    .order-card {
        border-radius: 12px;
    }
</style>

<?php include 'includes/footer.php'; ?>
