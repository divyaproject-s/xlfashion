<?php
session_start();
include 'includes/config.php';

/* -------------------------------------------------------------
   GET CATEGORY FROM URL
-------------------------------------------------------------- */
$rawCat = isset($_GET['cat']) ? trim($_GET['cat']) : '';

$categoryName = "";
$categoryId = 0;

if ($rawCat !== '') {

    // Get category by slug or name
    $stmt = $conn->prepare("
        SELECT id, name 
        FROM categories 
        WHERE slug = ? OR name = ?
        LIMIT 1
    ");

    $stmt->bind_param("ss", $rawCat, $rawCat);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($cat = $res->fetch_assoc()) {
        $categoryName = $cat['name'];
        $categoryId = $cat['id'];
    }

    $stmt->close();
}

include 'includes/header.php';
?>

<div class="container my-5">

    <h2 class="text-center fw-bold mb-4 gradient-text">
        <?= $categoryName ? htmlspecialchars($categoryName) : "Shop by Category"; ?>
    </h2>

    <div class="row g-4 mt-4">

        <?php
        /* -------------------------------------------------------------
           LOAD PRODUCTS THAT BELONG TO THIS CATEGORY
        -------------------------------------------------------------- */
        if ($categoryId > 0) {
            // Fetch user wishlist if logged in
            $user_wishlist = [];
            if (isset($_SESSION['user_id'])) {
                $uid = $_SESSION['user_id'];
                $wish_res = $conn->query("SELECT product_id FROM wishlist WHERE user_id = $uid");
                while ($w = $wish_res->fetch_assoc()) {
                    $user_wishlist[] = $w['product_id'];
                }
            }

            $stmt = $conn->prepare("
        SELECT id, name, sgd_price, usual_price, image, image_url, sizes 
        FROM products 
        WHERE category_id = ? 
        ORDER BY id DESC
    ");

            $stmt->bind_param("i", $categoryId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $img = "assets/images/" . $row['image'];
                    $u_price = floatval($row['usual_price']);
                    $s_price = floatval($row['sgd_price']);

                    $usual_price_html = '';
                    if ($u_price > $s_price) {
                        $usual_price_html = '<small class="text-muted text-decoration-line-through me-2">SGD ' . number_format($u_price, 2) . '</small>';
                    }

                    $has_sizes_bool = !empty($row['sizes']);
                    // Buy Now → Checkout (direct purchase) or Product page (if sizes needed)
                    $buy_url = $has_sizes_bool ? "product.php?id=" . $row['id'] : "checkout.php?buy_now=" . $row['id'] . "&size=N/A";
                    // Add to Cart → Add item and go to cart page
                    $cart_url = $has_sizes_bool ? "product.php?id=" . $row['id'] : "cart.php?action=add&id=" . $row['id'] . "&size=N/A";

                    $is_wishlisted = in_array($row['id'], $user_wishlist) ? 'active' : '';
                $img = $row['image'];
                $img_url = $row['image_url'] ?? '';
                $img_src = !empty($img_url) ? $img_url : (!empty($img) ? "assets/images/" . $img : "");
                
                echo '
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 product-card shadow-sm border-0" onclick="window.location.href=\'product.php?id=' . $row['id'] . '\'" style="cursor:pointer;">
                        <div class="position-relative overflow-hidden">
                            <img src="' . $img_src . '" 
                                 class="card-img-top product-img" 
                                 alt="' . htmlspecialchars($row['name']) . '" 
                                 style="height:250px;object-fit:cover;">
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2 p-2">New</span>
                        <button class="wishlist-btn ' . $is_wishlisted . '" onclick="toggleWishlist(event, ' . $row['id'] . ', this)" title="Add to Wishlist">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>

                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="card-title">' . htmlspecialchars($row['name']) . '</h5>
                        <div class="mb-3">
                             ' . $usual_price_html . '
                             <span class="text-danger fw-bold fs-5">SGD ' . number_format($s_price, 2) . '</span>
                        </div>

                        <div class="mt-auto d-grid gap-2">
                             <button onclick="handleAction(event, \'' . $buy_url . '\')" class="btn btn-dark btn-sm rounded-pill fw-bold">Buy Now</button>
                             <button onclick="handleAction(event, \'' . $cart_url . '\')" class="btn btn-outline-dark btn-sm rounded-pill">Add to Cart</button>
                        </div>
                    </div>

                </div>
            </div>';
                }
            } else {
                echo "<p class='text-center text-muted'>No products found in this category.</p>";
            }
            $stmt->close();
        } else {
            echo "<p class='text-center text-muted'>Please select a category.</p>";
        }
        ?>

    </div>
</div>

<script>
function handleAction(event, url) {
    event.stopPropagation();
    if (!isLoggedIn) {
        showLoginModal(() => {
            window.location.href = url;
        });
        return;
    }
    window.location.href = url;
}
</script>

<?php include 'includes/footer.php'; ?>