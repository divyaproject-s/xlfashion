<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/config.php';

$message = "";

/* --------------------------------------------------
   FETCH ALL CATEGORIES
---------------------------------------------------*/
$cat_query = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

$categories = [];
while ($row = $cat_query->fetch_assoc()) {
    $categories[$row['id']] = $row['name'];
}

/* --------------------------------------------------
   CATEGORIES THAT DO NOT HAVE SIZE
---------------------------------------------------*/
$no_size_categories = ["sarees", "saree", "bags", "bag"];  

$no_size_cat_ids = [];
foreach ($categories as $id => $name) {
    if (in_array(strtolower($name), $no_size_categories)) {
        $no_size_cat_ids[] = $id;
    }
}

/* --------------------------------------------------
   FORM SUBMIT
---------------------------------------------------*/
if (isset($_POST['submit'])) {

    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $description = trim($_POST['description']);
    $fabric = trim($_POST['fabric']);
    $usual_price = floatval($_POST['usual_price']);
    $sgd_price = floatval($_POST['sgd_price']);
    $image_name = "";

    $is_no_size = in_array($category_id, $no_size_cat_ids);

    /* -----------------------------------------------
       SIZE / NO SIZE HANDLING
    --------------------------------------------------*/
    if ($is_no_size) {
        // Saree / Bags
        $sizes_str = "";
        $total_stock = intval($_POST['stock']);
        $size_stock_json = json_encode(["total" => $total_stock]);

    } else {

        if (empty($_POST['sizes'])) {
            $message = "<div class='alert alert-danger'>Select at least one size.</div>";
        } else {
            $sizes = $_POST['sizes'];
            $sizes_str = implode(",", $sizes);

            $size_stock = [];
            $total_stock = 0;

            foreach ($sizes as $s) {
                $st = intval($_POST["stock_$s"] ?? 0);
                $size_stock[$s] = $st;
                $total_stock += $st;
            }

            $size_stock_json = json_encode($size_stock);
        }
    }

    /* -----------------------------------------------
       IMAGE HANDLING (UPLOAD OR URL)
    --------------------------------------------------*/
    if (!$message) {
        $image_url = trim($_POST['image_url'] ?? '');
        
        if (!empty($_FILES['image']['name'])) {
            $allowed_ext = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed_ext)) { // Corrected from original instruction's typo: !in_array($allowed_ext, $allowed_ext)
                $message = "<div class='alert alert-danger'>Invalid image format.</div>";
            } else {
                $image_name = time() . "_" . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $_FILES['image']['name']);
                $path = "../assets/images/" . $image_name;
                move_uploaded_file($_FILES['image']['tmp_name'], $path);
            }
        }

        if (empty($image_name) && empty($image_url)) {
            $message = "<div class='alert alert-danger'>Please upload an image or provide an Image URL.</div>";
        }
    }

    /* -----------------------------------------------
       INSERT INTO DATABASE
    --------------------------------------------------*/
    if (!$message) {

        $stmt = $conn->prepare("
            INSERT INTO products 
            (name, category_id, description, fabric, usual_price, sgd_price, stock, sizes, size_stock, image, image_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sissddissss",
            $name,
            $category_id,
            $description,
            $fabric,
            $usual_price,
            $sgd_price,
            $total_stock,
            $sizes_str,
            $size_stock_json,
            $image_name,
            $image_url
        );

        if ($stmt->execute()) {
            $_SESSION['msg'] = "<div class='alert alert-success'>Product added successfully!</div>";
            header("Location: add_product.php");
            exit;
        } else {
            $message = "<div class='alert alert-danger'>DB ERROR: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}

if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    unset($_SESSION['msg']);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Product</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
.size-stock-input { display:none; margin-top:5px; }
</style>
</head>

<body class="bg-light">

<div class="container py-5">

    <h2>Add Product</h2>
    <a href="admin_index.php" class="btn btn-outline-primary mb-3">⬅ Back</a>

    <?= $message ?>

    <div class="card p-4 shadow-sm">

<form method="post" enctype="multipart/form-data">

    <div class="mb-3">
        <label class="form-label">Product Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Category</label>
        <select name="category_id" id="categorySelect" class="form-select" required>
            <option value="">-- Select Category --</option>
            <?php foreach ($categories as $id => $name): ?>
                <option value="<?= $id ?>"><?= $name ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Fabric</label>
        <input type="text" name="fabric" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Usual Price</label>
        <input type="number" name="usual_price" step="0.01" class="form-control">
    </div>

    <div class="mb-3">
        <label class="form-label">Offer Price (SGD)</label>
        <input type="number" name="sgd_price" step="0.01" class="form-control" required>
    </div>

    <!-- Sizes section -->
    <div class="mb-3" id="size-section">
        <label class="form-label">Sizes & Stock</label><br>

        <?php 
        $all_sizes = ["S","M","L","XL","XXL","3XL","4XL"];
        foreach ($all_sizes as $s): ?>
            <label>
                <input type="checkbox" class="sizeCheck" name="sizes[]" value="<?= $s ?>" 
                       onclick="toggleStock('stock_<?= $s ?>')"> <?= $s ?>
            </label>

            <input type="number" min="0" name="stock_<?= $s ?>" id="stock_<?= $s ?>" 
                   class="form-control size-stock-input" placeholder="Stock for <?= $s ?>">
        <?php endforeach; ?>
    </div>

    <!-- No size section -->
    <div class="mb-3" id="no-size-section" style="display:none;">
        <label>Total Stock</label>
        <input type="number" min="0" class="form-control" name="stock">
    </div>

    <div class="mb-3">
        <label class="form-label fw-bold">Product Image</label>
        <div class="card p-3 border-light bg-light">
            <div class="mb-2">
                <label class="form-label small text-muted">Upload File</label>
                <input type="file" name="image" class="form-control">
            </div>
            <div class="text-center my-2 fw-bold text-muted small">OR</div>
            <div>
                <label class="form-label small text-muted">Paste Image URL</label>
                <input type="text" name="image_url" class="form-control" placeholder="https://example.com/image.jpg">
            </div>
        </div>
    </div>

    <button class="btn btn-success" name="submit">Add Product</button>
</form>

    </div>
</div>

<script>
const noSize = <?= json_encode($no_size_cat_ids) ?>;

function adjust() {
    let selected = parseInt(document.getElementById("categorySelect").value);

    if (noSize.includes(selected)) {
        document.getElementById("size-section").style.display = "none";
        document.getElementById("no-size-section").style.display = "block";
    } else {
        document.getElementById("size-section").style.display = "block";
        document.getElementById("no-size-section").style.display = "none";
    }
}

function toggleStock(id) {
    let el = document.getElementById(id);
    el.style.display = (el.style.display === "none" || el.style.display === "") ? "block" : "none";
}

document.getElementById("categorySelect").addEventListener("change", adjust);
</script>

</body>
</html>
