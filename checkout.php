<?php
session_start();
include 'includes/config.php';

// Optional: require login
// if (empty($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$cart = $_SESSION['cart'] ?? [];
$errors = [];
$notice = "";

// Calculate totals up-front (also used for display)
function cartTotals($cart) {
    $grand = 0.0;
    foreach ($cart as $pid => $item) {
        $qty = isset($item['qty']) ? (int)$item['qty'] : (int)($item['quantity'] ?? 1);
        if ($qty < 1) $qty = 1;
        $price = isset($item['price']) ? (float)$item['price'] : 0.0;
        $grand += $qty * $price;
    }
    return $grand;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {

    if (empty($cart)) {
        $errors[] = "Your cart is empty.";
    } else {
        // Start transaction
        $conn->begin_transaction();
        try {
            $user_id = $_SESSION['user_id'] ?? null; // allow null if guest orders are OK
            $total_amount = cartTotals($cart);

            // Create order
            $stmtOrder = $conn->prepare(
                "INSERT INTO orders (user_id, total_amount, created_at) VALUES (?, ?, NOW())"
            );
            // user_id can be null -> use "i" but allow null with bind_param by using null variable
            $stmtOrder->bind_param("id", $user_id, $total_amount);
            $stmtOrder->execute();
            $order_id = $stmtOrder->insert_id;
            $stmtOrder->close();

            // Insert items
            $stmtItem = $conn->prepare(
                "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)"
            );

            foreach ($cart as $pid => $item) {
                $product_id = (int)$pid;
                $qty   = isset($item['qty']) ? (int)$item['qty'] : (int)($item['quantity'] ?? 1);
                if ($qty < 1) $qty = 1;
                $price = isset($item['price']) ? (float)$item['price'] : 0.0;

                $stmtItem->bind_param("iiid", $order_id, $product_id, $qty, $price);
                $stmtItem->execute();
            }
            $stmtItem->close();

            // Commit
            $conn->commit();

            // Clear cart
            $_SESSION['cart'] = [];

            // Redirect to success page
            header("Location: order_success.php?id=" . $order_id);
            exit;

        } catch (Throwable $e) {
            $conn->rollback();
            $errors[] = "Order failed. Please try again. (" . htmlspecialchars($e->getMessage()) . ")";
        }
    }
}

// For display
$grandTotal = cartTotals($cart);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout - Alankara</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: #f8f9fa; }
    .checkout-card {
        max-width: 1000px;
        margin: 40px auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .checkout-header {
        background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
        color: #fff;
        padding: 18px 24px;
        font-weight: 600;
        font-size: 1.1rem;
    }
    .product-thumb {
        width: 60px; height: 60px; object-fit: cover; border-radius: 8px;
    }
    .btn-gradient {
        background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
        color: #fff; border: none; border-radius: 40px; padding: 10px 22px;
        transition: transform .2s ease, box-shadow .2s ease;
        font-weight: 600;
    }
    .btn-gradient:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(124,14,32,.35); color:#fff; }
    .btn-outline-danger { border-radius: 40px; }
</style>
</head>
<body>

<div class="checkout-card">
    <div class="checkout-header">
        🧾 Checkout
    </div>

    <div class="p-4">

        <?php if ($errors): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart)): ?>
            <div class="text-center py-5">
                <p class="mb-3">Your cart is empty.</p>
                <a href="products.php" class="btn btn-gradient">Continue Shopping</a>
            </div>
        <?php else: ?>

            <!-- Order Summary -->
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th width="120">Price (₹)</th>
                            <th width="110">Qty</th>
                            <th width="140">Total (₹)</th>
                            <th width="110">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $pid => $item): 
                            $name  = htmlspecialchars($item['name'] ?? 'Product');
                            $image = htmlspecialchars($item['image'] ?? '');
                            $qty   = isset($item['qty']) ? (int)$item['qty'] : (int)($item['quantity'] ?? 1);
                            if ($qty < 1) $qty = 1;
                            $price = isset($item['price']) ? (float)$item['price'] : 0.0;
                            $total = $qty * $price;
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($image): ?>
                                        <img src="assets/images/<?= $image ?>" class="product-thumb" alt="<?= $name ?>">
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-semibold"><?= $name ?></div>
                                        <small class="text-muted">#<?= (int)$pid ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= number_format($price, 2) ?></td>
                            <td><?= $qty ?></td>
                            <td class="fw-semibold"><?= number_format($total, 2) ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-danger"
                                   href="cart.php?action=remove&id=<?= (int)$pid ?>">Remove</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-light">
                            <td colspan="3" class="text-end fw-semibold">Grand Total</td>
                            <td class="fw-bold"><?= number_format($grandTotal, 2) ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between">
                <a href="cart.php?action=clear" class="btn btn-outline-danger">Clear Cart</a>
                <form method="post">
                    <button type="submit" name="place_order" class="btn btn-gradient">
                        Place Order
                    </button>
                </form>
            </div>

        <?php endif; ?>
    </div>
</div>

</body>
</html>
