<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$product_id = intval($_GET['id'] ?? 0);
if ($product_id <= 0) {
    $_SESSION['msg'] = "<div class='alert alert-danger'>Invalid product ID.</div>";
    header("Location: manage_products.php");
    exit;
}

/* Fetch product */
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    $_SESSION['msg'] = "<div class='alert alert-danger'>Product not found.</div>";
    header("Location: manage_products.php");
    exit;
}

/* Fetch categories (to allow changing category if needed) */
$catRes = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = [];
while ($c = $catRes->fetch_assoc()) $categories[$c['id']] = $c['name'];

/* Determine which categories are no-size (Sarees, Bags) */
$no_size_names = ['sarees', 'saree', 'bags', 'bag'];
$no_size_cat_ids = [];
foreach ($categories as $cid => $cname) {
    if (in_array(strtolower($cname), $no_size_names)) $no_size_cat_ids[] = $cid;
}

/* Form submission handling */
if (isset($_POST['update_product'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sgd_price = floatval($_POST['sgd_price'] ?? 0);
    $fabric = trim($_POST['fabric'] ?? '');
    $usual_price = floatval($_POST['usual_price'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? $product['category_id']);

    $is_no_size = in_array($category_id, $no_size_cat_ids);

    if ($is_no_size) {
        // single stock
        $stock = intval($_POST['stock'] ?? 0);
        $sizes_str = "";
        $size_stock_json = json_encode(['stock' => $stock]);
    } else {
        // sizes
        $sizes = $_POST['sizes'] ?? [];
        $sizes_str = implode(",", $sizes);
        $size_stock_arr = [];
        $total_stock = 0;
        foreach ($sizes as $s) {
            $st = intval($_POST["stock_$s"] ?? 0);
            $size_stock_arr[$s] = $st;
            $total_stock += $st;
        }
        $size_stock_json = json_encode($size_stock_arr);
        $stock = $total_stock;
    }

    // image handling (keep old if not provided)
    $image = $product['image'];
    $image_url = trim($_POST['image_url'] ?? '');

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $newImage = time() . "_" . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $_FILES['image']['name']);
            $target = "../assets/images/" . $newImage;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                // delete old image if it was a local file
                if (!empty($product['image']) && file_exists("../assets/images/" . $product['image'])) {
                    @unlink("../assets/images/" . $product['image']);
                }
                $image = $newImage;
            }
        }
    }

    // Update product
    $upd = $conn->prepare("
        UPDATE products SET
            name = ?, description = ?, sgd_price = ?, usual_price = ?, fabric = ?, category_id = ?,
            stock = ?, sizes = ?, size_stock = ?, image = ?, image_url = ?
        WHERE id = ?
    ");
    $upd->bind_param(
        "ssddsiissssi",
        $name,
        $description,
        $sgd_price,
        $usual_price,
        $fabric,
        $category_id,
        $stock,
        $sizes_str,
        $size_stock_json,
        $image,
        $image_url,
        $product_id
    );
    if ($upd->execute()) {
        $_SESSION['msg'] = "<div class='alert alert-success'>Product updated successfully.</div>";
        $upd->close();
        header("Location: manage_products.php");
        exit;
    } else {
        $error = $upd->error;
        $upd->close();
        $message = "<div class='alert alert-danger'>DB Error: " . htmlspecialchars($error) . "</div>";
    }
}

// Prepare data for form
$size_stock = json_decode($product['size_stock'], true) ?: [];
$has_sizes_now = !in_array($product['category_id'], $no_size_cat_ids);
$sizes_list = $product['sizes'] ? explode(",", $product['sizes']) : ['S','M','L','XL','2XL','3XL','4XL','5XL'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
.size-input { width:120px; display:inline-block; margin-right:8px; margin-bottom:6px; }
</style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Edit Product #<?= $product_id ?></h4>
        <a href="manage_products.php" class="btn btn-secondary">← Back</a>
    </div>

    <?php if (!empty($message)) echo $message; ?>
    <?php if (isset($_SESSION['msg'])) { echo $_SESSION['msg']; unset($_SESSION['msg']); } ?>

    <div class="card p-4 shadow-sm">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" id="categorySelect" class="form-select" required>
                    <?php foreach ($categories as $cid => $cname): ?>
                        <option value="<?= $cid ?>" <?= ($product['category_id'] == $cid) ? 'selected' : '' ?>><?= htmlspecialchars($cname) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Fabric</label>
                <input type="text" name="fabric" class="form-control" value="<?= htmlspecialchars($product['fabric']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Usual Price</label>
                <input type="number" name="usual_price" step="0.01" class="form-control" value="<?= htmlspecialchars($product['usual_price']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Offer Price (SGD)</label>
                <input type="number" name="sgd_price" step="0.01" class="form-control" value="<?= htmlspecialchars($product['sgd_price']) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Product Image</label><br>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <?php 
                    $curr_img = $product['image'];
                    $curr_url = $product['image_url'];
                    $img_src = !empty($curr_url) ? $curr_url : (!empty($curr_img) ? "../assets/images/" . $curr_img : "");
                    if (!empty($img_src)): 
                    ?>
                        <div class="text-center">
                            <img src="<?= htmlspecialchars($img_src) ?>" width="120" class="rounded shadow-sm d-block mb-1">
                            <small class="text-muted">Current View</small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card p-3 border-light bg-light flex-grow-1">
                        <div class="mb-2">
                            <label class="form-label small text-muted fw-bold">Upload New File</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="text-center my-1 fw-bold text-muted small">OR</div>
                        <div>
                            <label class="form-label small text-muted fw-bold">Update Image URL</label>
                            <input type="text" name="image_url" class="form-control" 
                                   value="<?= htmlspecialchars($product['image_url'] ?? '') ?>" 
                                   placeholder="https://example.com/image.jpg">
                        </div>
                    </div>
                </div>
            </div>

            <!-- dynamic size / stock section -->
            <div id="size-area" class="mb-3">
                <?php if ($has_sizes_now): ?>
                    <label class="form-label">Sizes & Stock</label>
                    <div>
                        <?php
                        // show known sizes in form; if product has sizes, use them; else use default
                        $sizes_for_form = $product['sizes'] ? explode(",", $product['sizes']) : ['S','M','L','XL','2XL','3XL','4XL','5XL'];
                        foreach ($sizes_for_form as $sz): 
                            $sz = trim($sz);
                            $val = intval($size_stock[$sz] ?? 0);
                        ?>
                            <div class="mb-2">
                                <label style="width:60px;display:inline-block;"><?= htmlspecialchars($sz) ?></label>
                                <input type="number" name="stock_<?= htmlspecialchars($sz) ?>" value="<?= $val ?>" min="0" class="form-control" style="width:120px;display:inline-block;">
                                <input type="hidden" name="sizes[]" value="<?= htmlspecialchars($sz) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" value="<?= intval($size_stock['stock'] ?? $product['stock'] ?? 0) ?>" min="0">
                <?php endif; ?>
            </div>

            <button class="btn btn-primary" name="update_product">Update Product</button>
        </form>
    </div>
</div>

<script>
const noSizeCatIds = <?= json_encode($no_size_cat_ids) ?>;
const categorySelect = document.getElementById('categorySelect');
const sizeArea = document.getElementById('size-area');

function toggleSizeArea() {
    const selected = parseInt(categorySelect.value);
    // if new selected category is no-size => show single stock input
    if (noSizeCatIds.includes(selected)) {
        // reload page to simplify logic (so server renders single stock input). Alternatively you can switch UI dynamically.
        // We'll reload with the same id so server will render appropriate form.
        const params = new URLSearchParams(window.location.search);
        params.set('id', <?= $product_id ?>);
        // preserve message? no need
        window.location.search = params.toString();
    } else {
        // reload same way to show sizes; this approach keeps server-side rendering clean and consistent
        const params = new URLSearchParams(window.location.search);
        params.set('id', <?= $product_id ?>);
        window.location.search = params.toString();
    }
}

// Optional: listen change and reload
categorySelect.addEventListener('change', toggleSizeArea);
</script>
</body>
</html>
