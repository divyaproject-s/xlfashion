<?php
session_start();
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Placed - Alankara</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .success-card {
        max-width: 700px; margin: 60px auto; background: #fff;
        border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,.08);
        padding: 40px; text-align: center;
    }
    .btn-gradient {
        background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
        color: #fff; border: none; border-radius: 40px; padding: 10px 22px;
        font-weight: 600;
    }
</style>
</head>
<body>
<div class="success-card">
    <h3 class="mb-3">🎉 Thank You!</h3>
    <p>Your order has been placed successfully.</p>
    <?php if ($order_id): ?>
        <p class="text-muted">Order ID: <strong>#<?= $order_id ?></strong></p>
    <?php endif; ?>
    <div class="mt-4">
        <a href="index.php" class="btn btn-gradient">Continue Shopping</a>
    </div>
</div>
</body>
</html>
