<?php
session_start();
include 'includes/config.php';
include 'includes/header.php';

$order = null;
$error = "";

if (isset($_GET['order_id']) && isset($_GET['mobile'])) {
    $oid = intval($_GET['order_id']);
    $mobile = trim($_GET['mobile']);

    $stmt = $conn->prepare("SELECT o.*, u.mobile FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND u.mobile = ?");
    $stmt->bind_param("is", $oid, $mobile);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if (!$order) {
        $error = "Order not found. Please check your Order ID and Mobile Number.";
    }
}
?>

<div class="container my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold display-5 gradient-text">Track Your Order</h2>
        <p class="text-muted">Enter your details below to see the current status of your shipment.</p>
        <div class="divider mx-auto"></div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 shadow rounded-4 p-4 mb-4">
                <form action="track_order.php" method="GET">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">Order ID</label>
                        <input type="number" name="order_id" class="form-control border-0 bg-light py-2 px-3" placeholder="e.g. 10234" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted">Registered Mobile Number</label>
                        <input type="tel" name="mobile" class="form-control border-0 bg-light py-2 px-3" placeholder="10-digit number" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill fw-bold py-2 shadow-sm">Check Status <i class="bi bi-search ms-1"></i></button>
                    </div>
                </form>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger rounded-3"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($order): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 animate__animated animate__fadeIn">
                    <h5 class="fw-bold mb-4">Order Update: #<?= $order['id'] ?></h5>
                    
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge bg-<?= $order['status'] == 'delivered' ? 'success' : ($order['status'] == 'pending' ? 'warning' : 'primary') ?> px-3">
                            <?= ucfirst($order['status']) ?>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="text-muted">Order Date</span>
                        <span class="fw-bold text-dark"><?= date('d M, Y', strtotime($order['created_at'])) ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <span class="text-muted">Total Amount</span>
                        <span class="fw-bold text-primary">SGD <?= number_format($order['total_amount'], 2) ?></span>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="text-muted small">Need more help? <a href="contact.php" class="text-decoration-none">Contact Support</a></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.gradient-text {
    background: linear-gradient(45deg, #2c3e50, #000000);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.divider {
    width: 60px;
    height: 4px;
    background: #ff1493;
    border-radius: 2px;
}
.btn-primary {
    background-color: #ff1493 !important;
    border-color: #ff1493 !important;
}
.btn-primary:hover {
    background-color: #e91e63 !important;
    border-color: #e91e63 !important;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 20, 147, 0.3);
}
.text-primary {
    color: #ff1493 !important;
}
.badge.bg-primary {
    background-color: #ff1493 !important;
}
</style>

<?php include 'includes/footer.php'; ?>
