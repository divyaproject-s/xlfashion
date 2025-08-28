<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_alert'] = 'Please login or signup to continue with your purchase.';
    header("Location: login.php");
    exit();
}
include 'includes/config.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle actions
$action = $_GET['action'] ?? '';

if ($action === 'add' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch product from DB
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        // If product already in cart, increase qty
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name'  => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'qty'   => 1
            ];
        }
    }
    header("Location: cart.php");
    exit;
}

if ($action === 'remove' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit;
}

if ($action === 'clear') {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}
?>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
    <h2 class="mb-4 text-center fw-bold gradient-text">
        <i class="bi bi-cart-fill"></i> My Shopping Cart
    </h2>

    <?php if (!empty($_SESSION['cart'])): ?>
        <div class="table-responsive shadow-lg rounded">
            <table class="table table-hover align-middle">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php $grandTotal = 0; ?>
                <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                    <?php $total = $item['price'] * $item['qty']; $grandTotal += $total; ?>
                    <tr class="cart-row">
                        <td class="d-flex align-items-center">
                            <img src="assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                 width="70" height="70" 
                                 class="me-3 rounded shadow-sm border">
                            <span class="fw-semibold"><?= htmlspecialchars($item['name']) ?></span>
                        </td>
                        <td class="text-center">₹ <?= number_format($item['price'], 2) ?></td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6 px-3 py-2"><?= $item['qty'] ?></span>
                        </td>
                        <td class="text-center fw-bold text-success">₹ <?= number_format($total, 2) ?></td>
                        <td class="text-center">
                            <a href="cart.php?action=remove&id=<?= $id ?>" 
                               class="btn btn-sm btn-outline-danger px-3">
                               <i class="bi bi-trash-fill"></i> Remove
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-warning fw-bold">
                    <td colspan="3" class="text-end fs-5">Grand Total</td>
                    <td colspan="2" class="fs-5 text-success">₹ <?= number_format($grandTotal, 2) ?></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="cart.php?action=clear" class="btn btn-danger px-4">
                <i class="bi bi-x-circle-fill"></i> Clear Cart
            </a>
            <a href="checkout.php" class="btn btn-success px-4">
                <i class="bi bi-credit-card-fill"></i> Checkout
            </a>
        </div>

    <?php else: ?>
        <div class="alert alert-info shadow-sm text-center py-4 fs-5">
            <i class="bi bi-info-circle"></i> Your cart is empty.  
            <br><a href="products.php" class="btn btn-sm btn-primary mt-3">🛍 Continue Shopping</a>
        </div>
    <?php endif; ?>
</div>

<style>
    .gradient-text {
        background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .cart-row:hover {
        background: #fff7f9 !important;
        transition: 0.3s ease-in-out;
    }
</style>

<?php include 'includes/footer.php'; ?>
