<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/config.php';

$message = "";

// Handle form submission
if (isset($_POST['submit'])) {
    $name        = trim($_POST['name']);
    $category    = trim($_POST['category']);
    $description = trim($_POST['description']);
    $price       = floatval($_POST['price']);

    if ($price <= 0) {
        $message = "<p style='color:red;'>Price must be greater than 0.</p>";
    } elseif (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_size = $_FILES['image']['size'];

        $upload_dir = '../assets/images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (!in_array($file_ext, $allowed_ext)) {
            $message = "<p style='color:red;'>Invalid image format. Allowed: JPG, JPEG, PNG, GIF.</p>";
        } elseif ($file_size > 2 * 1024 * 1024) {
            $message = "<p style='color:red;'>Image is too large. Max size: 2MB.</p>";
        } else {
            $image_name = time() . "_" . preg_replace("/[^a-zA-Z0-9\._-]/", "_", $_FILES['image']['name']);
            $image_path = $upload_dir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO products (name, category, description, price, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssds", $name, $category, $description, $price, $image_name);

                if ($stmt->execute()) {
                    $_SESSION['msg'] = "<p style='color:green;'>Product added successfully!</p>";
                    header("Location: add_product.php");
                    exit;
                } else {
                    $message = "<p style='color:red;'>Database error: " . $stmt->error . "</p>";
                }
                $stmt->close();
            } else {
                $message = "<p style='color:red;'>Image upload failed!</p>";
            }
        }
    } else {
        $message = "<p style='color:red;'>Please select a product image.</p>";
    }
}

// Get flash message
if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    unset($_SESSION['msg']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="bg-white p-4 rounded shadow">
        <h2 class="mb-4">Add New Product</h2>
        <div class="mb-3"><?= $message ?></div>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Product Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    <option value="Jewellery Set">Jewellery Set</option>
                    <option value="Earrings & Studs">Earrings & Studs</option>
                    <option value="Bangles">Bangles</option>
                    <option value="Necklaces">Necklaces</option>
                    <option value="Rings">Rings</option>
                    <option value="Anklets">Anklets</option>
                    <option value="Oxidised">Oxidised</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Price (INR)</label>
                <input type="number" name="price" step="0.01" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Product Image</label>
                <input type="file" name="image" class="form-control" accept="image/*" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Add Product</button>
        </form>
        <div class="mt-3">
            <a href="manage_products.php" class="btn btn-outline-secondary">📦 Manage Products</a>
        </div>
    </div>
</div>
</body>
</html>
