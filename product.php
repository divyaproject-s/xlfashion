<?php
session_start();
include 'includes/config.php';

// Get Product ID
if (!isset($_GET['id'])) {
    die("Product ID not provided.");
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("Product not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> - Alankara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background: #f9f9f9; }
        .product-detail {
            max-width: 1000px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .product-image img {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        }
        .product-info h2 { font-weight: bold; margin-bottom: 15px; }
        .price { color: #e60073; font-size: 26px; font-weight: bold; }
        .btn-cart {
            background: linear-gradient(90deg, #7c0e20ff, #fc1978ff);
            color: #fff;
            font-weight: bold;
            border-radius: 25px;
            padding: 12px 25px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .btn-cart:hover {
            background: linear-gradient(90deg, #fc1978ff, #7c0e20ff);
            color: #fff;
            transform: translateY(-2px);
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #333;
        }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="product-detail row">
        <!-- Product Image -->
        <div class="col-md-5 product-image">
            <img src="assets/images/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <!-- Product Info -->
        <div class="col-md-7 product-info">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <?php
            $avg_rating_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM product_reviews WHERE product_id = ?");
            $avg_rating_stmt->bind_param("i", $id);
            $avg_rating_stmt->execute();
            $avg_rating_result = $avg_rating_stmt->get_result();
            $avg_rating = $avg_rating_result->fetch_assoc()['avg_rating'] ?? 0;
            $avg_rating_stmt->close();

            if ($avg_rating > 0) {
                echo '<div class="mb-2">';
                for ($i = 0; $i < 5; $i++) {
                    echo ($i < round($avg_rating)) ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-warning"></i>';
                }
                echo ' <span class="text-muted">(' . number_format($avg_rating, 1) . ' / 5)</span>';
                echo '</div>';
            }
            ?>
            <p class="price">₹ <?= number_format($product['price'], 2) ?></p>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>

            <!-- Add to Cart -->
            <a href="cart.php?action=add&id=<?= $product['id'] ?>" class="btn-cart">
                🛒 Add to Cart
            </a>

            <br>
            <a href="products.php" class="back-link">← Back to Products</a>
        </div>
    </div>
</div>

<div class="container mt-5">
    <h3>Customer Reviews</h3>
    <div id="reviews-section">
        <?php
        $reviews_stmt = $conn->prepare("SELECT pr.rating, pr.comment, pr.created_at, u.name AS user_name 
                                        FROM product_reviews pr
                                        JOIN users u ON pr.user_id = u.id
                                        WHERE pr.product_id = ?
                                        ORDER BY pr.created_at DESC");
        $reviews_stmt->bind_param("i", $id);
        $reviews_stmt->execute();
        $reviews_result = $reviews_stmt->get_result();

        if ($reviews_result->num_rows > 0) {
            while ($review = $reviews_result->fetch_assoc()) {
                echo '<div class="card mb-3">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . htmlspecialchars($review['user_name']) . ' - ';
                for ($i = 0; $i < 5; $i++) {
                    echo ($i < $review['rating']) ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-warning"></i>';
                }
                echo '</h5>';
                echo '<h6 class="card-subtitle mb-2 text-muted">' . date('F j, Y, g:i a', strtotime($review['created_at'])) . '</h6>';
                echo '<p class="card-text">' . nl2br(htmlspecialchars($review['comment'])) . '</p>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No reviews yet. Be the first to review this product!</p>';
        }
        $reviews_stmt->close();
        ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <h4 class="mt-5">Submit Your Review</h4>
        <form id="review-form" class="mb-5">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
            <div class="mb-3">
                <label for="rating" class="form-label">Rating</label>
                <select class="form-select" id="rating" name="rating" required>
                    <option value="">Select a rating</option>
                    <option value="5">5 Stars - Excellent</option>
                    <option value="4">4 Stars - Very Good</option>
                    <option value="3">3 Stars - Good</option>
                    <option value="2">2 Stars - Fair</option>
                    <option value="1">1 Star - Poor</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="comment" class="form-label">Comment</label>
                <textarea class="form-control" id="comment" name="comment" rows="4"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Review</button>
        </form>
    <?php else: ?>
        <div class="alert alert-info mt-5">
            Please <a href="login.php">login</a> to submit a review.
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('review-form')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const searchParams = new URLSearchParams();
    for (const pair of formData) {
        searchParams.append(pair[0], pair[1]);
    }

    fetch('submit_review.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: searchParams.toString(),
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting your review.');
    });
});
</script>
</body>
</html>
