<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT image_path FROM carousel_images WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['image_path'])) {
            $file = "../assets/carousel/" . $row['image_path'];
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        $conn->query("DELETE FROM carousel_images WHERE id=$id");
    }
    header("Location: carousel.php");
    exit;
}

// Fetch Images
$images = $conn->query("SELECT * FROM carousel_images ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Carousel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .carousel-img-preview {
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🎠 Manage Carousel</h2>
            <div>
                <a href="add_carousel.php" class="btn btn-primary">➕ Add New Image</a>
                <a href="admin_index.php" class="btn btn-outline-secondary">⬅ Dashboard</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Title / Caption</th>
                            <th>Link</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($images->num_rows > 0): ?>
                            <?php while ($row = $images->fetch_assoc()): ?>
                                <tr>
                                    <td style="width: 150px; text-align: center;">
                                        <?php 
                                        $img_path = $row['image_path'];
                                        $img_url = $row['image_url'] ?? '';
                                        $img_src = !empty($img_url) ? $img_url : (!empty($img_path) ? "../assets/carousel/" . $img_path : "");
                                        ?>
                                        <img src="<?= htmlspecialchars($img_src) ?>"
                                            class="carousel-img-preview" alt="Slide">
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['title'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['link'] ?? '-') ?>
                                    </td>
                                    <td>
                                        <a href="carousel.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this slide?');">❌
                                            Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No carousel images found. Add one!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>