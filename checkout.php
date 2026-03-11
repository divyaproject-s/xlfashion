<?php
session_start();
include 'includes/config.php';

// Handle Buy Now
if (isset($_GET['buy_now']) && isset($_GET['size'])) {
    $pid = intval($_GET['buy_now']);
    $size = $_GET['size'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $prod = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($prod) {
        $size_stock = json_decode($prod['size_stock'], true);

        // Determine stock
        if ($size === "N/A") {
            $stock = $size_stock['total'] ?? ($size_stock['stock'] ?? 0);
        } else {
            $stock = $size_stock[$size] ?? 0;
        }

        // For Buy Now, we replace the cart with this single item
        $_SESSION['cart'] = [];
        $cartKey = $pid . '_' . $size;

        $_SESSION['cart'][$cartKey] = [
            'id' => $pid,
            'name' => $prod['name'],
            'description' => $prod['description'],
            'price' => $prod['sgd_price'],
            'image' => $prod['image'],
            'image_url' => $prod['image_url'],
            'size' => $size,
            'qty' => 1,
            'stock' => $stock,
            'size_stock' => $prod['size_stock']
        ];
    }
    // Redirect to remove query params so refresh doesn't re-add
    header("Location: checkout.php");
    exit;
}

// Redirect if cart is empty
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

// Get user info
$user_id = $_SESSION['user_id'] ?? null;
$user_address = "";
if ($user_id) {
    $res = $conn->query("SELECT address FROM users WHERE id=$user_id LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        $user_address = $row['address'];
    }
}

// Calculate totals
function cartTotals($cart)
{
    $grand = 0.0;
    foreach ($cart as $item) {
        $qty = max(1, (int) ($item['qty'] ?? 1));
        $price = (float) ($item['price'] ?? 0);
        $grand += $qty * $price;
    }
    return $grand;
}
$grandTotal = cartTotals($cart);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Checkout - XL Fashion Trends</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="CSS/checkout.css?v=<?= time(); ?>">
</head>

<body>

    <div class="checkout-card">
        <div class="checkout-header">🧾 Checkout</div>
        <div class="checkout-body">
            <div class="checkout-table-wrapper mb-4">
                <table class="checkout-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price ($)</th>
                            <th>Qty</th>
                            <th>Total ($)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <?php foreach ($cart as $key => $item):
                            $name = htmlspecialchars($item['name'] ?? 'No Name');
                            $desc = htmlspecialchars($item['description'] ?? '');
                            $image = htmlspecialchars($item['image'] ?? '');
                            $size = htmlspecialchars($item['size'] ?? 'N/A');
                            $qty = max(1, (int) ($item['qty'] ?? 1));
                            $price = (float) ($item['price'] ?? 0);
                            $stock = $item['stock'] ?? 0;
                            $total = $qty * $price;
                            ?>
                            <tr data-key="<?= $key ?>" data-price="<?= $price ?>" data-stock="<?= $stock ?>">
                                <td data-label="Product">
                                    <div class="product-cell">
                                        <?php 
                                        $image = $item['image'] ?? '';
                                        $image_url = $item['image_url'] ?? '';
                                        if ($image || $image_url): 
                                            $img_src = !empty($image_url) ? $image_url : "assets/images/" . $image;
                                        ?>
                                            <img src="<?= $img_src ?>" class="product-thumb" alt="<?= $name ?>">
                                        <?php endif; ?>
                                        <div class="product-info">
                                            <h5><?= $name ?></h5>
                                            <small><?= nl2br($desc) ?></small>
                                            <div class="product-badges">
                                                <span class="badge bg-dark">Size: <?= $size ?></span>
                                                <?php if ($stock > 0): ?>
                                                    <span class="badge bg-success">Stock: <?= $stock ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Price" class="price-cell">$<?= number_format($price, 2) ?></td>
                                <td data-label="Qty" class="qty-cell">
                                    <div class="qty-box">
                                        <button class="btn btn-sm btn-outline-secondary minus">-</button>
                                        <input type="text" class="form-control qty" value="<?= $qty ?>" readonly>
                                        <button class="btn btn-sm btn-outline-secondary plus">+</button>
                                    </div>
                                </td>
                                <td data-label="Total" class="total-cell">$<?= number_format($total, 2) ?></td>
                                <td data-label="Action">
                                    <button class="btn btn-sm btn-outline-danger remove-item">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="checkout-grand-total">
                            <td colspan="3" class="grand-total-label">Grand Total</td>
                            <td class="grand-total-value" id="grand-total">$<?= number_format($grandTotal, 2) ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Delivery Address -->
            <form method="post" action="checkout_process.php">
                <div class="checkout-section">
                    <h5>📦 Delivery Address</h5>
                    
                    <div class="address-option">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="address_option" id="defaultAddr" value="default" checked>
                            <label class="form-check-label" for="defaultAddr">
                                Use my registered address
                            </label>
                        </div>
                        <div class="registered-address"><?= htmlspecialchars($user_address) ?></div>
                    </div>

                    <div class="address-option">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="address_option" id="newAddr" value="new">
                            <label class="form-check-label" for="newAddr">Deliver to another address</label>
                        </div>
                        <textarea name="new_address" id="new_address" class="form-control mt-2" rows="3" placeholder="Enter new delivery address" disabled></textarea>
                    </div>
                </div>

                <div class="checkout-actions">
                    <a href="cart.php?action=clear" class="btn btn-outline-danger">Clear Cart</a>
                    <button type="submit" name="place_order" class="btn btn-gradient">Place Order</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll(".qty-box").forEach(box => {
            const minusBtn = box.querySelector(".minus");
            const plusBtn = box.querySelector(".plus");
            const qtyInput = box.querySelector(".qty");
            const row = box.closest("tr");
            const key = row.dataset.key;
            const stock = parseInt(row.dataset.stock);

            function updateTotals() {
                const price = parseFloat(row.dataset.price);
                const qty = parseInt(qtyInput.value);
                row.querySelector(".total-cell").textContent = "$" + (price * qty).toFixed(2);

                let grand = 0;
                document.querySelectorAll("#cart-body tr:not(.checkout-grand-total)").forEach(r => {
                    const tc = r.querySelector(".total-cell");
                    if (tc) grand += parseFloat(tc.textContent.replace("$", ""));
                });
                document.getElementById("grand-total").textContent = "$" + grand.toFixed(2);
            }

            function saveToServer(qty) {
                fetch("update_cart.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "key=" + encodeURIComponent(key) + "&qty=" + qty
                }).then(res => res.json())
                    .then(data => {
                        if (data.status === 'removed') row.remove();
                        updateTotals();
                    });
            }

            plusBtn.addEventListener("click", () => {
                let current = parseInt(qtyInput.value);
                if (current < stock) {
                    qtyInput.value = current + 1;
                    updateTotals();
                    saveToServer(qtyInput.value);
                } else {
                    alert("Cannot exceed available stock!");
                }
            });

            minusBtn.addEventListener("click", () => {
                let current = parseInt(qtyInput.value);
                if (current > 1) {
                    qtyInput.value = current - 1;
                    updateTotals();
                    saveToServer(qtyInput.value);
                }
            });
        });

        // Enable/disable address textarea
        document.querySelectorAll('input[name="address_option"]').forEach(radio => {
            radio.addEventListener("change", () => {
                const newAddr = document.getElementById("new_address");
                newAddr.disabled = !document.getElementById("newAddr").checked;
            });
        });

        // Remove item
        document.querySelectorAll(".remove-item").forEach(btn => {
            btn.addEventListener("click", e => {
                const row = e.target.closest("tr");
                const key = row.dataset.key;
                fetch("update_cart.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "key=" + encodeURIComponent(key) + "&qty=0"
                }).then(res => res.json())
                    .then(data => {
                        if (data.status === 'removed') row.remove();
                        let grand = 0;
                        document.querySelectorAll(".total-cell").forEach(tc => {
                            grand += parseFloat(tc.textContent.replace("$", ""));
                        });
                        document.getElementById("grand-total").textContent = "$" + grand.toFixed(2);
                    });
            });
        });
    </script>

</body>

</html>