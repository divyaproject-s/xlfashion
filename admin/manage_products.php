<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch all categories
$categoriesResult = $conn->query("SELECT id, name, image, image_url FROM categories ORDER BY name ASC");
$categories = [];
$categoryImages = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[$row['id']] = $row['name'];
    $img = $row['image'];
    $url = $row['image_url'] ?? '';
    $categoryImages[$row['id']] = !empty($url) ? $url : (!empty($img) ? "../assets/category/" . $img : "");
}

// Get selected category from GET
$selectedCategoryId = intval($_GET['category'] ?? 0);
if (!$selectedCategoryId && !empty($categories)) {
    $selectedCategoryId = array_key_first($categories);
}

// Fetch products for selected category
$products = [];
if ($selectedCategoryId) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id=? ORDER BY id DESC");
    $stmt->bind_param("i", $selectedCategoryId);
    $stmt->execute();
    $res = $stmt->get_result();
    $products = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .category-btn {
            margin: 5px;
            display: inline-block;
            text-align: center;
        }

        .category-btn img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 50%;
        }

        .product-card img {
            height: 150px;
            object-fit: cover;
        }

        .size-stock {
            font-size: 0.9rem;
            color: #555;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>📦 Manage Products</h2>
            <a href="admin_index.php" class="btn btn-outline-primary">⬅ Back to Dashboard</a>
        </div>

        <!-- Category Buttons -->
        <div class="mb-4">
            <?php foreach ($categories as $id => $name): ?>
                <?php 
                $cat_src = $categoryImages[$id];
                ?>
                <a href="?category=<?= $id ?>" class="category-btn btn btn-light shadow-sm">
                    <img src="<?= htmlspecialchars($cat_src) ?>"
                        alt="<?= htmlspecialchars($name) ?>"><br>
                    <?= htmlspecialchars($name) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Products Grid -->
        <div class="row">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $p): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card product-card shadow-sm">
                            <?php 
                            $prod_img = $p['image'];
                            $prod_url = $p['image_url'] ?? '';
                            $prod_src = !empty($prod_url) ? $prod_url : (!empty($prod_img) ? "../assets/images/" . $prod_img : "");
                            ?>
                            <img src="<?= htmlspecialchars($prod_src) ?>" class="card-img-top"
                                alt="<?= htmlspecialchars($p['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($p['name']) ?></h5>
                                <p class="card-text">SGD <?= number_format($p['sgd_price'], 2) ?></p>

                                <?php
                                // Display stock info
                                $size_stock = json_decode($p['size_stock'], true);
                                echo '<div class="size-stock">';
                                if (!empty($p['sizes']) && is_array($size_stock)) {
                                    foreach ($size_stock as $size => $stock) {
                                        echo htmlspecialchars($size) . ': ' . intval($stock) . '<br>';
                                    }
                                } else {
                                    echo 'Stock: ' . intval($p['stock']);
                                }
                                echo '</div>';
                                ?>

                                <div class="mt-2">
                                    <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="delete_product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger"
                                        onclick="return confirm('Delete this product?');">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No products in this category.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>