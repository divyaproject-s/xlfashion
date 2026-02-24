<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../includes/config.php';

if (!isset($_GET['id'])) {
    header("Location: manage_curated_looks.php");
    exit;
}

$id = intval($_GET['id']);
$look_res = $conn->query("SELECT * FROM curated_looks WHERE id = $id");
$look = $look_res->fetch_assoc();

if (!$look) {
    header("Location: manage_curated_looks.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_look'])) {
    $title = $_POST['title'];
    $link = $_POST['link'];
    $order = intval($_POST['display_order']);
    $status = $_POST['status'];
    
    // Handle Image Update (Upload or URL)
    $image_name = $look['image_path']; // Current local image path
    $image_url = trim($_POST['image_url'] ?? ''); // New image URL from form

    $target_dir = "../assets/curated/";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
        $new_image_name = time() . "_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $new_image_name;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            // Delete old image if it's not a URL
            if (!empty($look['image_path']) && file_exists("../assets/curated/" . $look['image_path'])) {
                @unlink("../assets/curated/" . $look['image_path']);
            }
            $image_name = $new_image_name;
        }
    }

    $stmt = $conn->prepare("UPDATE curated_looks SET title = ?, image_path = ?, image_url = ?, link = ?, display_order = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssisi", $title, $image_name, $image_url, $link, $order, $status, $id);
    if ($stmt->execute()) {
        $_SESSION['msg'] = "Curated look updated successfully!";
        header("Location: manage_curated_looks.php");
        exit;
    } else {
        $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Curated Look</title>
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
            <h2 class="fw-bold">✏️ Edit Curated Look</h2>
            <a href="manage_curated_looks.php" class="btn btn-outline-primary">⬅ Back to List</a>
        </div>

        <?= $message ?>

        <div class="card p-4">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row align-items-center mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold d-block">Current Image</label>
                        <?php 
                        $curr_img = $look['image_path'];
                        $curr_url = $look['image_url'];
                        $img_src = !empty($curr_url) ? $curr_url : (!empty($curr_img) ? "../assets/curated/" . $curr_img : "");
                        ?>
                        <img src="<?= $img_src ?>" class="rounded shadow-sm" style="width: 120px; height: 160px; object-fit: cover;">
                    </div>
                    <div class="col-md-9">
                        <label class="form-label fw-bold">Update Image</label>
                        <div class="card p-3 border-light bg-light">
                            <div class="mb-2">
                                <label class="form-label small text-muted">Upload New File</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <div class="text-center my-1 fw-bold text-muted small">OR</div>
                            <div>
                                <label class="form-label small text-muted">Update Image URL</label>
                                <input type="text" name="image_url" class="form-control" 
                                       value="<?= htmlspecialchars($look['image_url'] ?? '') ?>" 
                                       placeholder="https://example.com/look.jpg">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($look['title']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">“Shop All” Link</label>
                    <input type="text" name="link" class="form-control" value="<?= htmlspecialchars($look['link']) ?>" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Display Order</label>
                        <input type="number" name="display_order" class="form-control" value="<?= $look['display_order'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $look['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $look['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="update_look" class="btn btn-primary px-5">Update Curated Look</button>
            </form>
        </div>
    </div>
</body>
</html>
