<?php
session_start();
include "includes/config.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?show_login=1");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch wishlisted products
$query = "SELECT p.* FROM products p 
          JOIN wishlist w ON p.id = w.product_id 
          WHERE w.user_id = ? 
          ORDER BY w.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

include "includes/header.php";
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">My <span class="text-danger">Wishlist</span></h2>
        <a href="index.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">Continue Shopping</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
            <?php 
                $img = $row['image'];
                $img_url = $row['image_url'] ?? '';
                $img_src = !empty($img_url) ? $img_url : (!empty($img) ? "assets/images/" . $img : "");
                $u_price = floatval($row['usual_price']);
                $s_price = floatval($row['sgd_price']);
                $has_sizes_bool = !empty($row['sizes']);
                $buy_url = $has_sizes_bool ? "product.php?id=" . $row['id'] : "checkout.php?buy_now=" . $row['id'];
                $cart_url = $has_sizes_bool ? "product.php?id=" . $row['id'] : "cart.php?action=add&id=" . $row['id'] . "&size=N/A";
            ?>
                <div class="col-6 col-md-4 col-lg-3" id="wishlist-item-<?= $row['id'] ?>">
                    <div class="card product-card h-100 border-0 shadow-sm position-relative">
                        <div class="position-relative overflow-hidden product-img-wrapper" onclick="window.location.href='product.php?id=<?= $row['id'] ?>'" style="cursor: pointer;">
                            <img src="<?= $img_src ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" style="height:250px;object-fit:cover;">
                            <button class="wishlist-btn active" onclick="removeFromWishlistPage(event, <?= $row['id'] ?>)" title="Remove from Wishlist">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>
                        <div class="card-body p-3 d-flex flex-column text-center">
                            <h6 class="card-title fw-bold text-truncate mb-2"><?= htmlspecialchars($row['name']) ?></h6>
                            <div class="mb-3">
                                <?php if ($u_price > $s_price): ?>
                                    <small class="text-muted text-decoration-line-through me-2">SGD <?= number_format($u_price, 2) ?></small>
                                <?php endif; ?>
                                <span class="text-danger fw-bold fs-5">SGD <?= number_format($s_price, 2) ?></span>
                            </div>
                            <div class="mt-auto d-grid gap-2">
                                <button onclick="handleAction(event, '<?= $buy_url ?>')" class="btn btn-dark btn-sm rounded-pill fw-bold">Buy Now</button>
                                <button onclick="handleAction(event, '<?= $cart_url ?>')" class="btn btn-outline-dark btn-sm rounded-pill">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-heart-break text-muted" style="font-size: 4rem;"></i>
            </div>
            <h4 class="text-muted">Your wishlist is empty</h4>
            <p class="text-secondary mb-4">Save items you love to find them here later!</p>
            <a href="index.php" class="btn btn-danger px-4 py-2 rounded-pill fw-bold">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<script>
function removeFromWishlistPage(event, productId) {
    if (event) event.stopPropagation();
    
    // Use the global toggleWishlist but handle UI removal here
    const btn = event.currentTarget;
    
    fetch('ajax_wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.action === 'removed') {
            const item = document.getElementById('wishlist-item-' + productId);
            if (item) {
                item.style.transition = 'all 0.5s ease';
                item.style.opacity = '0';
                item.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    item.remove();
                    // Check if wishlist is now empty
                    if (document.querySelectorAll('[id^="wishlist-item-"]').length === 0) {
                        window.location.reload();
                    }
                }, 500);
            }
        } else {
            alert(data.message || 'Error removal');
        }
    });
}
</script>

<?php include "includes/footer.php"; ?>
