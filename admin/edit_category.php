<?php
session_start();
include '../includes/config.php';

// Admin check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_categories.php");
    exit();
}
$id = (int)$_GET['id'];

// Fetch category
$stmt = $conn->prepare("SELECT * FROM categories WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: manage_categories.php");
    exit();
}
$cat = $result->fetch_assoc();
$stmt->close();

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $msg = "Category name cannot be empty.";
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        // Check uniqueness
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name=? AND id != ?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $msg = "Category name already exists.";
        }
        $stmt->close();
    }

    // Handle image
    $image_name = $cat['image'];
    $image_url = trim($_POST['image_url'] ?? '');

    if (empty($msg)) {
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $msg = "Only JPG, PNG, GIF, WEBP images are allowed.";
            } else {
                $new_image = time() . "_" . uniqid() . "." . $ext;
                $target = "../assets/category/" . $new_image;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $msg = "Failed to upload image.";
                } else {
                    // Delete old local image if any
                    if (!empty($cat['image']) && file_exists("../assets/category/" . $cat['image'])) {
                        @unlink("../assets/category/" . $cat['image']);
                    }
                    $image_name = $new_image;
                }
            }
        }
    }

    // Update DB
    if (empty($msg)) {
        $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, image=?, image_url=? WHERE id=?");
        $stmt->bind_param("ssssi", $name, $slug, $image_name, $image_url, $id);
        if ($stmt->execute()) {
            header("Location: manage_categories.php");
            exit();
        } else {
            $msg = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!-- HTML Form -->
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Category</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h2>Edit Category</h2>
    <?php if ($msg): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Category Name:</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($cat['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold">Current Image:</label><br>
            <?php 
            $img_src = !empty($cat['image_url']) ? $cat['image_url'] : (!empty($cat['image']) ? "../assets/category/" . $cat['image'] : "");
            if ($img_src): 
            ?>
                <img src="<?= htmlspecialchars($img_src) ?>" width="120" class="rounded shadow-sm mb-2">
            <?php else: ?>
                <span class="text-secondary">No image</span>
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label class="form-label fw-bold text-dark">Category Image</label>
            <div class="card p-3 border-light bg-light">
                <div class="mb-2">
                    <label class="form-label small text-muted">Upload New File (Optional)</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <div class="text-center my-1 fw-bold text-muted small">OR</div>
                <div>
                    <label class="form-label small text-muted">Update Image URL</label>
                    <input type="text" name="image_url" class="form-control" 
                           value="<?= htmlspecialchars($cat['image_url'] ?? '') ?>" 
                           placeholder="https://example.com/item.jpg">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Update Category</button>
        <a href="manage_categories.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
