<?php
session_start();
include 'includes/config.php';

// Get Product ID
if (!isset($_GET['id']))
    die("Product ID not provided.");
$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
if (!$product)
    die("Product not found.");

// Sizes & stock
$has_sizes = !empty($product['sizes']);
$sizes = $has_sizes ? explode(",", $product['sizes']) : [];
$size_stock = $has_sizes ? json_decode($product['size_stock'], true) : [];
$total_stock = !$has_sizes ? intval($product['stock']) : 0;

// PAGINATION SETTINGS
$reviews_per_page = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $reviews_per_page;

// Get total reviews count
$stmt_count = $conn->prepare("SELECT COUNT(*) AS total FROM product_reviews WHERE product_id = ?");
$stmt_count->bind_param("i", $id);
$stmt_count->execute();
$total_reviews = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();

$total_pages = ceil($total_reviews / $reviews_per_page);


// Fetch reviews
$reviewsStmt = $conn->prepare("SELECT r.*, u.name as user_name FROM product_reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id=? ORDER BY r.created_at DESC");
$reviewsStmt->bind_param("i", $id);
$reviewsStmt->execute();
$reviewsResult = $reviewsStmt->get_result();
$reviews = $reviewsResult->fetch_all(MYSQLI_ASSOC);
$reviewsStmt->close();

// Fetch user's previous review (if logged in)
$user_review = null;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $stmt_prev = $conn->prepare("SELECT rating, comment FROM product_reviews WHERE product_id = ? AND user_id = ?");
    $stmt_prev->bind_param("ii", $id, $uid);
    $stmt_prev->execute();
    $result_prev = $stmt_prev->get_result();
    $user_review = $result_prev->fetch_assoc();
    $stmt_prev->close();
}


// Calculate average rating
$avg_rating = 0;
if (count($reviews) > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avg_rating = $sum / count($reviews);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> - XL Fashion Trends</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


    <link rel="stylesheet" href="CSS/product.css">
    <style>


    </style>
</head>

<body>

    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="product-detail row">

            <!-- IMAGE -->
            <div class="col-md-5 position-relative">
                <div class="product-image">
                    <?php
                    $curr_img = $product['image'];
                    $curr_url = $product['image_url'] ?? '';
                    $img_src = !empty($curr_url) ? $curr_url : (!empty($curr_img) ? "assets/images/" . $curr_img : "");
                    ?>
                    <img src="<?= htmlspecialchars($img_src) ?>" class="img-fluid rounded-3 shadow-sm"
                        alt="<?= htmlspecialchars($product['name']) ?>">
                </div>

                <?php
                $is_wishlisted = '';
                if (isset($_SESSION['user_id'])) {
                    $uid = $_SESSION['user_id'];
                    $pid = $product['id'];
                    $wish_check = $conn->query("SELECT id FROM wishlist WHERE user_id = $uid AND product_id = $pid");
                    if ($wish_check->num_rows > 0) {
                        $is_wishlisted = 'active';
                    }
                }
                ?>
                <button class="wishlist-btn <?= $is_wishlisted ?>"
                    onclick="toggleWishlist(event, <?= $product['id'] ?>, this)" title="Add to Wishlist"
                    style="top: 20px; right: 20px; width: 45px; height: 45px;">
                    <i class="bi bi-heart" style="font-size: 1.5rem;"></i>
                </button>
            </div>

            <!-- PRODUCT INFO -->
            <div class="col-md-7">
                <h2><?= htmlspecialchars($product['name']) ?></h2>

                <!-- PRICE -->
                <div class="mb-2">
                    <?php
                    $u_price = floatval($product['usual_price']);
                    $s_price = floatval($product['sgd_price']);
                    if ($u_price > $s_price):
                        ?>
                        <span class="text-muted text-decoration-line-through me-2 fs-5">SGD
                            <?= number_format($u_price, 2) ?></span>
                    <?php endif; ?>
                    <span class="fw-bold text-danger fs-4">SGD <?= number_format($product['sgd_price'], 2) ?></span>
                </div>

                <?php if (!empty($product['fabric'])): ?>
                    <p><strong>Fabric:</strong> <?= htmlspecialchars($product['fabric']) ?></p>
                <?php endif; ?>

                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

                <form action="cart.php" method="GET" onsubmit="return checkLogin(event)">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                    <?php if ($has_sizes): ?>
                        <h5 class="fw-bold mt-3">Select Size</h5>
                        <select name="size" class="form-select w-50 mb-3" required>
                            <option value="">Choose Size</option>
                            <?php foreach ($sizes as $size):
                                $size = trim($size);
                                $stock = $size_stock[$size] ?? 0;
                                ?>
                                <option value="<?= $size ?>" <?= $stock == 0 ? "disabled" : "" ?>>
                                    <?= $size ?>         <?= $stock == 0 ? "(Out of Stock)" : "($stock left)" ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="hidden" name="size" value="N/A">
                        <h5 class="fw-bold mt-3">Stock Available</h5>
                        <p><?= $total_stock > 0 ? "$total_stock in stock" : "Out of stock" ?></p>
                    <?php endif; ?>

                    <button type="submit" class="btn-cart" <?= (!$has_sizes && $total_stock == 0) ? "disabled" : "" ?>>🛒
                        Add to Cart</button>
                </form>

                <?php if ($has_sizes): ?>
                    <button onclick="buyNow(<?= $product['id'] ?>)" class="btn btn-warning mt-3"
                        style="border-radius:25px; padding:12px 25px;">
                        ⚡ Buy Now
                    </button>
                <?php elseif ($total_stock > 0): ?>
                    <a href="checkout.php?buy_now=<?= $product['id'] ?>" onclick="return checkLogin(event)"
                        class="btn btn-warning mt-3" style="border-radius:25px; padding:12px 25px;">⚡ Buy Now</a>
                <?php endif; ?>

                <!-- AVERAGE RATING -->
                <div class="review-box mt-4">
                    <h5>⭐ Average Rating: <?= number_format($avg_rating, 1) ?> / 5</h5>
                    <?php
                    $fullStars = floor($avg_rating);
                    $halfStar = ($avg_rating - $fullStars) >= 0.5;
                    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                    for ($i = 0; $i < $fullStars; $i++)
                        echo '<i class="bi bi-star-fill"></i>';
                    if ($halfStar)
                        echo '<i class="bi bi-star-half"></i>';
                    for ($i = 0; $i < $emptyStars; $i++)
                        echo '<i class="bi bi-star"></i>';
                    ?>
                </div>

                <!-- REVIEWS LIST -->
                <div class="review-box mt-3">
                    <h5>Customer Reviews (<?= count($reviews) ?>)</h5>
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $rev): ?>
                            <div class="review">
                                <div class="user"><?= htmlspecialchars($rev['user_name']) ?>
                                    <span class="date"><?= date("d M Y", strtotime($rev['created_at'])) ?></span>
                                </div>
                                <div class="rating">
                                    <?php
                                    $r = intval($rev['rating']);
                                    for ($i = 0; $i < $r; $i++)
                                        echo '<i class="bi bi-star-fill"></i>';
                                    for ($i = $r; $i < 5; $i++)
                                        echo '<i class="bi bi-star"></i>';
                                    ?>
                                </div>
                                <div class="comment"><?= nl2br(htmlspecialchars($rev['comment'])) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No reviews yet. Be the first to review!</p>
                    <?php endif; ?>
                </div>

                <!-- SUBMIT REVIEW FORM -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="review-box mt-4">
                        <h5><?= $user_review ? "Update Your Review" : "Submit Your Review" ?></h5>

                        <form id="reviewForm">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                            <!-- Star Rating -->
                            <div class="mb-3">
                                <label class="mb-2">Your Rating:</label>
                                <div class="star-rating-input" id="starRating">
                                    <?php for ($r = 1; $r <= 5; $r++): ?>
                                        <i class="bi bi-star star-icon" data-value="<?= $r ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput"
                                    value="<?= $user_review['rating'] ?? '' ?>" required>
                            </div>

                            <!-- Comment -->
                            <div class="mb-2">
                                <label>Comment:</label>
                                <textarea name="comment" class="form-control" rows="3"
                                    required><?= $user_review['comment'] ?? '' ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-success">
                                <?= $user_review ? "Update Review" : "Submit Review" ?>
                            </button>

                            <div id="reviewMsg" class="mt-2"></div>
                        </form>
                    </div>
                <?php else: ?>
                    <p class="text-danger mt-3">
                        Please <a href="login.php">login</a> to submit a review.
                    </p>
                <?php endif; ?>


            </div>
        </div>
    </div>

    <script>
        function checkLogin(event) {
            if (!isLoggedIn) {
                if (event) event.preventDefault();

                // If it's a form submission, handle it
                if (event && event.type === 'submit') {
                    const form = event.target;
                    showLoginModal(() => {
                        form.submit();
                    });
                } else if (event && event.target.tagName === 'A') {
                    // If it's a link (like Buy Now for non-size products)
                    const url = event.target.href;
                    showLoginModal(() => {
                        window.location.href = url;
                    });
                } else {
                    // Fallback for other clicks
                    showLoginModal();
                }
                return false;
            }
            return true;
        }

        function buyNow(id) {
            if (!isLoggedIn) {
                showLoginModal(() => buyNow(id));
                return;
            }

            let sizeSelect = document.querySelector("select[name='size']");
            let size = sizeSelect ? sizeSelect.value : "N/A";
            if (sizeSelect && !size) { alert("Please select a size"); return; }
            window.location.href = "checkout.php?buy_now=" + id + "&size=" + size;
        }

        // Star Rating Logic
        document.addEventListener('DOMContentLoaded', function () {
            const starContainer = document.getElementById('starRating');
            if (!starContainer) return;

            const stars = starContainer.querySelectorAll('.star-icon');
            const ratingInput = document.getElementById('ratingInput');
            let currentRating = ratingInput.value;

            function updateStars(val) {
                stars.forEach(s => {
                    const sVal = s.getAttribute('data-value');
                    if (sVal <= val) {
                        s.classList.replace('bi-star', 'bi-star-fill');
                        s.classList.add('selected');
                    } else {
                        s.classList.replace('bi-star-fill', 'bi-star');
                        s.classList.remove('selected');
                    }
                });
            }

            if (currentRating) updateStars(currentRating);

            stars.forEach(star => {
                star.addEventListener('mouseover', function () {
                    updateStars(this.getAttribute('data-value'));
                });

                star.addEventListener('mouseout', function () {
                    updateStars(currentRating || 0);
                });

                star.addEventListener('click', function () {
                    currentRating = this.getAttribute('data-value');
                    ratingInput.value = currentRating;
                    updateStars(currentRating);
                });
            });
        });

        // Submit review via AJAX
        document.getElementById('reviewForm')?.addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            fetch('submit_review.php', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
                .then(res => res.json())
                .then(data => {
                    const msgDiv = document.getElementById('reviewMsg');
                    msgDiv.textContent = data.message;
                    msgDiv.className = data.success ? 'text-success mt-2' : 'text-danger mt-2';

                    if (data.success) {
                        setTimeout(() => { location.reload(); }, 1000);
                    }
                })
                .catch(err => console.error(err));
        });

    </script>



    <?php include 'includes/footer.php'; ?>
</body>

</html>