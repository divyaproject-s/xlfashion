<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Return a JSON response indicating the user is not logged in
    echo json_encode([
        'success' => false,
        'message' => 'Please login or signup to add items to your cart.',
        'login_redirect' => 'login.php',
        'signup_redirect' => 'signup.php'
    ]);
    exit();
}

// Add to cart logic here
require_once __DIR__ . '/includes/config.php';

$product_id = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? 1;

if ($product_id) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + ?");
    $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Product added to cart.']);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid product.']);
exit();
?>

<!-- Example Add to Cart Button -->
<button class="btn btn-primary add-to-cart" data-product-id="123">Add to Cart</button>

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
                if (data.login_redirect && data.signup_redirect) {
                    const userChoice = confirm(data.message + "\n\nClick OK to login or Cancel to signup.");
                    if (userChoice) {
                        window.location.href = data.login_redirect;
                    } else {
                        window.location.href = data.signup_redirect;
                    }
                } else {
                    alert(data.message);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    });
});
</script>