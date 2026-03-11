<?php
session_start();
include 'includes/config.php';

// --- Initialize cart ---
if (!isset($_SESSION['cart']))
    $_SESSION['cart'] = [];

$action = $_GET['action'] ?? '';

// --- ADD TO CART ---
if ($action === 'add' && isset($_GET['id'], $_GET['size'])) {
    $id = intval($_GET['id']);
    $size = $_GET['size'];
    $cartKey = $id . '_' . $size;

    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($product) {
        $size_stock = json_decode($product['size_stock'], true);
        if ($size === "N/A" || $size === "NOSIZE") {
            $stock = intval($product['stock']);
        } else {
            $stock = $size_stock[$size] ?? 0;
        }

        if (isset($_SESSION['cart'][$cartKey])) {
            if ($_SESSION['cart'][$cartKey]['qty'] < $stock) {
                $_SESSION['cart'][$cartKey]['qty']++;
            }
        } else {
            $_SESSION['cart'][$cartKey] = [
                'id' => $id,
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['sgd_price'],
                'image' => $product['image'],
                'image_url' => $product['image_url'],
                'size' => $size,
                'qty' => 1,
                'stock' => $stock,
                'size_stock' => $product['size_stock']
            ];

        }
    }

    header("Location: cart.php");
    exit;
}


// --- REMOVE ---
if ($action === 'remove' && isset($_GET['key'])) {
    unset($_SESSION['cart'][$_GET['key']]);
    header("Location: cart.php");
    exit;
}

// --- CLEAR ---
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit;
}

// --- Display cart ---
include 'includes/header.php';
$cart = $_SESSION['cart'];
?>

<link rel="stylesheet" href="CSS/cart.css?v=<?= time(); ?>">

<div class="container my-5">
    <h2 class="cart-header">🛒 My <span class="gradient-text">Shopping Cart</span></h2>

    <?php if (!empty($cart)): ?>
        <div class="cart-table-wrapper">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="cart-body">
                    <?php $grandTotal = 0; ?>
                    <?php foreach ($cart as $key => $item):
                        $price = $item['price'] ?? 0;
                        $qty = $item['qty'] ?? 1;
                        $total = $price * $qty;
                        $grandTotal += $total;

                        // Determine stock for this size
                        $size = $item['size'] ?? 'N/A';
                        $product_stock = 0;

                        if (!empty($item['size_stock'])) {
                            $size_stock_data = is_array($item['size_stock']) ? $item['size_stock'] : json_decode($item['size_stock'], true);
                            if (isset($size_stock_data[$size])) {
                                $product_stock = intval($size_stock_data[$size]);
                            } else {
                                // Fallback to captured stock for non-sized items
                                $product_stock = intval($item['stock'] ?? 0);
                            }
                        } else {
                            $product_stock = intval($item['stock'] ?? 0);
                        }
                        ?>
                        <tr data-key="<?= $key ?>" data-stock="<?= $product_stock ?>" data-price="<?= $price ?>">
                            <td class="cart-product-cell">
                                    <?php
                                    $image = $item['image'];
                                    $image_url = $item['image_url'] ?? '';
                                    $name = $item['name'];
                                    $img_src = !empty($image_url) ? $image_url : (!empty($image) ? "assets/images/" . $image : "");
                                    ?>
                                    <div class="cart-product-image">
                                        <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($name) ?>">
                                    </div>
                                <div class="cart-product-info">
                                    <h5><?= htmlspecialchars($item['name']) ?></h5>
                                    <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                                    <div class="cart-badges">
                                        <span class="badge bg-dark">Size: <?= htmlspecialchars($size) ?></span>
                                        <?php if ($product_stock > 0): ?>
                                            <span class="badge bg-success">Stock: <?= $product_stock ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="cart-price" data-label="Price">SGD <?= number_format($price, 2) ?></td>
                            <td data-label="Quantity">
                                <div class="qty-box">
                                    <button class="btn btn-sm btn-outline-secondary minus">−</button>
                                    <input type="text" class="qty" value="<?= $qty ?>" readonly>
                                    <button class="btn btn-sm btn-outline-secondary plus">+</button>
                                </div>
                            </td>
                            <td class="line-total" data-label="Total">SGD <?= number_format($total, 2) ?></td>
                            <td data-label="Action">
                                <button class="btn btn-sm btn-outline-danger remove-item">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="cart-grand-total">
                        <td colspan="3" class="grand-label">Grand Total</td>
                        <td colspan="2" class="grand-value" id="grand-total">SGD <?= number_format($grandTotal, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="cart-actions">
            <a href="cart.php?action=clear" class="btn btn-danger">Clear Cart</a>
            <a href="checkout.php" class="btn btn-success">Checkout</a>
        </div>
    <?php else: ?>
        <div class="cart-empty">
            <div class="alert alert-info">
                Your cart is empty. <a href="index.php">Continue Shopping</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Quantity update logic
    document.querySelectorAll(".qty-box").forEach(box => {
        const minus = box.querySelector(".minus");
        const plus = box.querySelector(".plus");
        const qtyInput = box.querySelector(".qty");
        const row = box.closest("tr");
        const key = row.dataset.key;
        const stock = parseInt(row.dataset.stock);

        function updateTotals() {
            const price = parseFloat(row.dataset.price);
            const qty = parseInt(qtyInput.value);
            const totalText = (price * qty).toFixed(2);
            row.querySelector(".line-total").textContent = "SGD " + totalText;

            let grand = 0;
            document.querySelectorAll("#cart-body tr").forEach(r => {
                const lt = r.querySelector(".line-total");
                if (lt) {
                    const value = parseFloat(lt.textContent.replace('SGD ', ''));
                    if (!isNaN(value)) grand += value;
                }
            });
            document.getElementById("grand-total").textContent = "SGD " + grand.toFixed(2);
        }

        function saveQty(qty) {
            fetch("update_cart.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "key=" + encodeURIComponent(key) + "&qty=" + qty
            }).then(res => res.json()).then(data => {
                if (data.status === 'removed') row.remove();
                updateTotals();
            });
        }

        plus.addEventListener("click", () => {
            let val = parseInt(qtyInput.value);
            if (val < stock) {
                qtyInput.value = val + 1;
                updateTotals();
                saveQty(val + 1);
            } else alert("Cannot exceed stock!");
        });

        minus.addEventListener("click", () => {
            let val = parseInt(qtyInput.value);
            if (val > 1) {
                qtyInput.value = val - 1;
                updateTotals();
                saveQty(val - 1);
            }
        });
    });

    // Remove button
    document.querySelectorAll(".remove-item").forEach(btn => {
        btn.addEventListener("click", e => {
            const row = e.target.closest("tr");
            const key = row.dataset.key;
            fetch("update_cart.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "key=" + encodeURIComponent(key) + "&qty=0"
            }).then(res => res.json()).then(data => {
                if (data.status === 'removed') row.remove();
                let grand = 0;
                document.querySelectorAll("#cart-body tr").forEach(r => {
                    const lt = r.querySelector(".line-total");
                    if (lt) {
                        const value = parseFloat(lt.textContent.replace('SGD ', ''));
                        if (!isNaN(value)) grand += value;
                    }
                });
                document.getElementById("grand-total").textContent = "SGD " + grand.toFixed(2);
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>