<?php
session_start();
include 'includes/config.php';

$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$order_items = [];
$grand_total = 0;

if ($order_id > 0) {
    $sql = "SELECT oi.quantity, oi.price, p.name, p.image, p.image_url
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $row['total'] = $row['quantity'] * $row['price'];
        $grand_total += $row['total'];
        $order_items[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Placed - XL Fashion Trends</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }

        .success-card {
            max-width: 900px;
            margin: 60px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
            padding: 40px;
        }

        .btn-gradient {
            background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
            color: #fff;
            border: none;
            border-radius: 40px;
            padding: 10px 22px;
            font-weight: 600;
        }

        .btn-gradient:hover {
            opacity: .9;
            color: #fff;
        }

        .product-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="success-card">
        <h3 class="mb-3 text-center">🎉 Thank You!</h3>
        <p class="text-center">Your order has been placed successfully.</p>

        <?php if ($order_id): ?>
            <p class="text-center text-muted">Order ID: <strong>#<?= $order_id ?></strong></p>
        <?php endif; ?>

        <?php if (!empty($order_items)): ?>
            <h5 class="mt-4">🛍 Your Order Details</h5>
            <div class="table-responsive mt-3">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th width="100">Qty</th>
                            <th width="120">Price ($)</th>
                            <th width="140">Total ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php 
                                        $curr_img = $item['image'];
                                        $curr_url = $item['image_url'] ?? '';
                                        $img_src = !empty($curr_url) ? $curr_url : (!empty($curr_img) ? "assets/images/" . $curr_img : "");
                                        if ($img_src): 
                                        ?>
                                            <img src="<?= htmlspecialchars($img_src) ?>" class="product-thumb"
                                                alt="<?= htmlspecialchars($item['name']) ?>">
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($item['name']) ?></span>
                                    </div>
                                </td>
                                <td><?= $item['quantity'] ?></td>
                                <td>$<?= number_format($item['price'], 2) ?></td>
                                <td class="fw-semibold">$<?= number_format($item['total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="table-secondary">
                            <td colspan="3" class="text-end fw-bold">Grand Total</td>
                            <td class="fw-bold">$<?= number_format($grand_total, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-gradient">🛒 Continue Shopping</a>
        </div>
    </div>
</body>

</html>