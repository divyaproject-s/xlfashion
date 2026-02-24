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
    <style>
        body {
            background: #f8f9fa;
        }

        .checkout-card {
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .checkout-header {
            background: linear-gradient(90deg, #c40e2c, #fc19787b);
            color: #fff;
            padding: 18px 24px;
            font-weight: 600;
        }

        .product-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }

        .qty-box {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .qty-box button {
            width: 32px;
            height: 32px;
            font-weight: bold;
        }

        .btn-gradient {
            background: linear-gradient(90deg, #c40e2c, #fc19787b);
            color: #fff;
            border: none;
            border-radius: 40px;
            padding: 10px 22px;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="checkout-card">
        <div class="checkout-header">🧾 Checkout</div>
        <div class="p-4">
            <div class="table-responsive mb-4">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Price ($)</th>
                            <th width="150">Qty</th>
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
                                <td>
                                    <div class="d-flex gap-3 align-items-start">
                                        <?php 
                                        $image = $item['image'] ?? '';
                                        $image_url = $item['image_url'] ?? '';
                                        if ($image || $image_url): 
                                            $img_src = !empty($image_url) ? $image_url : "assets/images/" . $image;
                                        ?>
                                            <img src="<?= $img_src ?>" class="product-thumb" alt="<?= $name ?>">
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= $name ?></strong><br>
                                            <small class="text-muted"><?= nl2br($desc) ?></small><br>
                                            <span class="badge bg-dark">Size: <?= $size ?></span>
                                            <?php if ($stock > 0): ?>
                                                <span class="badge bg-success">Stock: <?= $stock ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="price">$<?= number_format($price, 2) ?></td>
                                <td>
                                    <div class="qty-box">
                                        <button class="btn btn-sm btn-outline-secondary minus">-</button>
                                        <input type="text" class="form-control text-center qty" value="<?= $qty ?>"
                                            style="width:50px;" readonly>
                                        <button class="btn btn-sm btn-outline-secondary plus">+</button>
                                    </div>
                                </td>
                                <td class="line-total fw-semibold">$<?= number_format($total, 2) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger remove-item">Remove</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-light">
                            <td colspan="3" class="text-end fw-bold">Grand Total</td>
                            <td class="fw-bold" id="grand-total">$<?= number_format($grandTotal, 2) ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Delivery Address -->
            <form method="post" action="checkout_process.php">
                <h5>📦 Delivery Address</h5>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="address_option" id="defaultAddr" value="default"
                        checked>
                    <label class="form-check-label" for="defaultAddr">
                        Use my registered address:
                        <div class="border rounded p-2 mt-1 bg-light"><?= htmlspecialchars($user_address) ?></div>
                    </label>
                </div>
                <div class="form-check mt-3">
                    <input class="form-check-input" type="radio" name="address_option" id="newAddr" value="new">
                    <label class="form-check-label" for="newAddr">Deliver to another address:</label>
                    <textarea name="new_address" id="new_address" class="form-control mt-2" rows="3"
                        placeholder="Enter new delivery address" disabled></textarea>
                </div>

                <div class="d-flex justify-content-between mt-4">
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
                row.querySelector(".line-total").textContent = "$" + (price * qty).toFixed(2);

                let grand = 0;
                document.querySelectorAll("#cart-body tr").forEach(r => {
                    const lt = r.querySelector(".line-total");
                    if (lt) grand += parseFloat(lt.textContent.replace("$", ""));
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
                        document.querySelectorAll(".line-total").forEach(lt => {
                            grand += parseFloat(lt.textContent.replace("$", ""));
                        });
                        document.getElementById("grand-total").textContent = "$" + grand.toFixed(2);
                    });
            });
        });
    </script>

</body>

</html>