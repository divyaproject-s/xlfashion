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
<!-- The rest is same as your updated cart display code with plus/minus buttons -->


<div class="container my-5">
    <h2 class="mb-4 text-center fw-bold gradient-text">🛒 My Shopping Cart</h2>

    <?php if (!empty($cart)): ?>
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
                            <td class="d-flex align-items-start">
                                    <?php
                                    $image = $item['image'];
                                    $image_url = $item['image_url'] ?? '';
                                    $name = $item['name'];
                                    $img_src = !empty($image_url) ? $image_url : (!empty($image) ? "assets/images/" . $image : "");
                                    ?>
                                    <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($name) ?>" width="100" height="100"
                                    class="me-3 rounded shadow-sm border">
                                <div>
                                    <h5><?= htmlspecialchars($item['name']) ?></h5>
                                    <p class="text-muted mb-1"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                                    <span class="badge bg-dark">Size: <?= htmlspecialchars($size) ?></span>
                                    <?php if ($product_stock > 0): ?>
                                        <span class="badge bg-success">Stock: <?= $product_stock ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Out of Stock</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center"><?= number_format($price, 2) ?></td>
                            <td class="text-center">
                                <div class="qty-box">
                                    <button class="btn btn-sm btn-outline-secondary minus">-</button>
                                    <input type="text" class="form-control text-center qty" value="<?= $qty ?>"
                                        style="width:50px;" readonly>
                                    <button class="btn btn-sm btn-outline-secondary plus">+</button>
                                </div>
                            </td>
                            <td class="text-center line-total"><?= number_format($total, 2) ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger remove-item">Remove</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-light">
                        <td colspan="3" class="text-end fw-bold">Grand Total</td>
                        <td colspan="2" class="fw-bold text-success" id="grand-total"><?= number_format($grandTotal, 2) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="cart.php?action=clear" class="btn btn-danger px-4">Clear Cart</a>
            <a href="checkout.php" class="btn btn-success px-4">Checkout</a>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center py-4">
            Your cart is empty. <a href="index.php">Continue Shopping</a>
        </div>
    <?php endif; ?>
</div>

<script>
    // Quantity update logic (same as checkout.php)
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
            row.querySelector(".line-total").textContent = (price * qty).toFixed(2);

            let grand = 0;
            document.querySelectorAll("#cart-body tr").forEach(r => {
                const lt = r.querySelector(".line-total");
                if (lt) grand += parseFloat(lt.textContent);
            });
            document.getElementById("grand-total").textContent = grand.toFixed(2);
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
                    if (lt) grand += parseFloat(lt.textContent);
                });
                document.getElementById("grand-total").textContent = grand.toFixed(2);
            });
        });
    });
</script>

<?php include 'includes/footer.php'; ?>