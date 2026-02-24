<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../includes/config.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_look'])) {
    $title = $_POST['title'];
    $link = $_POST['link'];
    $order = intval($_POST['display_order']);
    $status = $_POST['status'];
    
    // Handle Image (Upload or URL)
    $image_name = "";
    $image_url = trim($_POST['image_url'] ?? '');

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/curated/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $image_name = time() . "_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $image_name;
        
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $message = "<div class='alert alert-danger'>Failed to upload image.</div>";
            $image_name = "";
        }
    }

    if (empty($message)) {
        if ($image_name == "" && $image_url == "") {
            $message = "<div class='alert alert-warning'>Please upload an image or provide a URL.</div>";
        } else {
            $stmt = $conn->prepare("INSERT INTO curated_looks (title, image_path, image_url, link, display_order, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssis", $title, $image_name, $image_url, $link, $order, $status);
            if ($stmt->execute()) {
                $_SESSION['msg'] = "Curated look added successfully!";
                header("Location: manage_curated_looks.php");
                exit;
            } else {
                $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Curated Look</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            background: #f8f9fa;
            margin: 0;
        }
        .main {
            flex: 1;
            padding: 30px;
            overflow-x: hidden;
        }
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">✨ Add Curated Look</h2>
            <a href="manage_curated_looks.php" class="btn btn-outline-primary">⬅ Back to List</a>
        </div>

        <?= $message ?>

        <div class="card p-4">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Title</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Office Wear, Party Wear" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">“Shop All” Link</label>
                    <input type="text" name="link" class="form-control" placeholder="e.g. category.php?cat=tops" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Display Order</label>
                    <input type="number" name="display_order" class="form-control" value="0">
                    <small class="text-muted">Lowest numbers appear first on homepage.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Curated Image</label>
                    <div class="card p-3 border-light bg-light">
                        <div class="mb-2">
                            <label class="form-label small text-muted">Upload File</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        <div class="text-center my-1 fw-bold text-muted small">OR</div>
                        <div>
                            <label class="form-label small text-muted">Paste Image URL</label>
                            <input type="text" name="image_url" class="form-control" placeholder="https://example.com/look.jpg">
                        </div>
                    </div>
                </div>

                <button type="submit" name="add_look" class="btn btn-primary px-5">Add Curated Look</button>
            </form>
        </div>
    </div>
</body>
</html>
