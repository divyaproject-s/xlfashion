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
    SELECT id, total_amount, status, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
?>

<div class="container my-5">
    <h2 class="mb-4 fw-bold text-center">🛒 My Orders</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if ($result_orders->num_rows > 0): ?>
        <?php while ($order = $result_orders->fetch_assoc()): ?>
            <div class="card shadow mb-4 order-card">
                <div class="card-header text-white fw-semibold d-flex justify-content-between align-items-center"
                     style="background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);">
                    <span>Order #<?= htmlspecialchars($order['id']) ?></span>
                    <span class="small"><?= date("d M Y, H:i", strtotime($order['created_at'])) ?></span>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo ($order['status'] === 'Completed') ? 'success' : 
                                         (($order['status'] === 'Pending') ? 'warning text-dark' : 'secondary'); 
                                ?>">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </p>
                            <p class="mb-0"><strong>Total:</strong> $<?= number_format($order['total_amount'], 2) ?></p>
                        </div>
                        <?php if ($order['status'] === 'Pending'): ?>
                            <a href="cancel_order.php?id=<?= $order['id'] ?>" 
                               class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-bold"
                               onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="bi bi-x-circle me-1"></i> Cancel Order
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Order Items -->
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th width="100">Size</th>
                                    <th width="100">Qty</th>
                                    <th width="120">Price ($)</th>
                                    <th width="140">Subtotal ($)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $order_id = $order['id'];
                                $items_stmt = $conn->prepare("
                                    SELECT p.name, p.image, p.image_url, oi.quantity, oi.price, oi.size
                                    FROM order_items oi
                                    JOIN products p ON oi.product_id = p.id
                                    WHERE oi.order_id = ?
                                ");

                                $items_stmt->bind_param("i", $order_id);
                                $items_stmt->execute();
                                $items_result = $items_stmt->get_result();

                                while ($item = $items_result->fetch_assoc()):
                                    $subtotal = $item['quantity'] * $item['price'];
                                ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php 
                                                $p_img = $item['image'];
                                                $p_url = $item['image_url'] ?? '';
                                                $p_src = !empty($p_url) ? $p_url : (!empty($p_img) ? "assets/images/" . $p_img : "");
                                                if (!empty($p_src)): 
                                                ?>
                                                    <img src="<?= htmlspecialchars($p_src) ?>" 
                                                         alt="<?= htmlspecialchars($item['name']) ?>" 
                                                         width="60" height="60" 
                                                         class="rounded shadow-sm border">
                                                <?php endif; ?>
                                                <span><?= htmlspecialchars($item['name']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($item['size'] ?? 'N/A') ?></td>
                                        <td><?= (int)$item['quantity'] ?></td>

                                        <!-- 🟢 Price changed to $ -->
                                        <td>$<?= number_format($item['price'], 2) ?></td>

                                        <!-- 🟢 Subtotal changed to $ -->
                                        <td><strong>$<?= number_format($subtotal, 2) ?></strong></td>
                                    </tr>
                                <?php endwhile; $items_stmt->close(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">
            You have not placed any orders yet.  
            <a href="index.php" class="btn btn-sm btn-gradient ms-2">Shop Now</a>
        </div>
    <?php endif; $stmt_orders->close(); ?>
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
.order-card { border-radius: 12px; }
</style>

<?php include 'includes/footer.php'; ?>
