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

<link rel="stylesheet" href="CSS/wishlist.css?v=<?= time(); ?>">

<div class="container my-5">
    <div class="wishlist-header">
        <h2>My <span>Wishlist</span></h2>
        <a href="index.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">Continue Shopping</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <div class="wishlist-container">
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
                <div id="wishlist-item-<?= $row['id'] ?>">
                    <div class="wishlist-card">
                        <div class="wishlist-card-img-wrapper" onclick="window.location.href='product.php?id=<?= $row['id'] ?>'" style="cursor: pointer;">
                            <img src="<?= $img_src ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                            <button class="wishlist-btn active" onclick="removeFromWishlistPage(event, <?= $row['id'] ?>)" title="Remove from Wishlist">
                                <i class="bi bi-heart-fill"></i>
                            </button>
                        </div>
                        <div class="wishlist-card-body">
                            <h6 class="wishlist-card-title"><?= htmlspecialchars($row['name']) ?></h6>
                            <div class="wishlist-price-group">
                                <?php if ($u_price > $s_price): ?>
                                    <span class="wishlist-usual-price">SGD <?= number_format($u_price, 2) ?></span>
                                <?php endif; ?>
                                <span class="wishlist-sale-price">SGD <?= number_format($s_price, 2) ?></span>
                            </div>
                            <div class="wishlist-actions">
                                <button onclick="handleAction(event, '<?= $buy_url ?>')" class="btn btn-dark btn-sm">Buy Now</button>
                                <button onclick="handleAction(event, '<?= $cart_url ?>')" class="btn btn-outline-dark btn-sm">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-wishlist">
            <div>
                <i class="bi bi-heart-break text-muted"></i>
                <h4>Your wishlist is empty</h4>
                <p>Save items you love to find them here later!</p>
                <a href="index.php" class="btn btn-danger">Start Shopping</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function handleAction(event, url) {
    if (event) event.preventDefault();
    
    // Check if user is logged in via global variable from header
    if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) {
        showLoginModal(() => {
            window.location.href = url;
        });
        return false;
    }
    
    window.location.href = url;
    return false;
}

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
