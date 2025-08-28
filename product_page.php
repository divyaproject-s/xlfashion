<?php
session_start();

// Example product data (replace with database query)
$product = [
    'id' => 123,
    'name' => 'Product Name',
    'price' => 1234,
    'rating' => 4.5 // Example rating out of 5
];
?>

<!-- Example Product Card -->
<div class="product-card">
    <h5><?= htmlspecialchars($product['name']) ?></h5>
    <p>₹<?= number_format($product['price'], 2) ?></p>

    <!-- Display Star Rating -->
    <div class="rating">
        <?php
        $fullStars = floor($product['rating']); // Number of full stars
        $halfStar = ($product['rating'] - $fullStars) >= 0.5; // Check if there's a half star
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0); // Remaining empty stars

        // Render full stars
        for ($i = 0; $i < $fullStars; $i++) {
            echo '<i class="bi bi-star-fill text-warning"></i>';
        }

        // Render half star
        if ($halfStar) {
            echo '<i class="bi bi-star-half text-warning"></i>';
        }

        // Render empty stars
        for ($i = 0; $i < $emptyStars; $i++) {
            echo '<i class="bi bi-star text-warning"></i>';
        }
        ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Show Add to Cart button if the user is logged in -->
        <button class="btn btn-primary add-to-cart" data-product-id="<?= $product['id'] ?>">Add to Cart</button>
    <?php else: ?>
        <!-- Show Please Register message if the user is not logged in -->
        <p class="text-danger">Please <a href="login.php" class="text-primary">login</a> or <a href="register.php" class="text-primary">register</a> to add items to your cart.</p>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function () {
        const productId = this.getAttribute('data-product-id');

        fetch('add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
            } else {
                alert(data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>

<!-- Include Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">