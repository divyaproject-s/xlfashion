<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Categories</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background: #f8f9fa; }
table img { border-radius: 8px; }
</style>
</head>
<body>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage Categories</h2>
        <a href="add_category.php" class="btn btn-primary">➕ Add Category</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="60">ID</th>
                        <th>Name</th>
                        <th width="150">Image</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td>
                            <?php 
                            $img_src = !empty($row['image_url']) ? $row['image_url'] : (!empty($row['image']) ? "../assets/category/" . $row['image'] : "");
                            if ($img_src): 
                            ?>
                                <img src="<?= $img_src ?>" width="80" class="rounded shadow-sm">
                            <?php else: ?>
                                <span class="text-secondary">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_category.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                ✏ Edit
                            </a>

                            <a href="delete_category.php?id=<?= $row['id'] ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Are you sure you want to delete this category?')">
                                🗑 Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>

            </table>

        </div>
    </div>

    <a href="admin_index.php" class="btn btn-secondary mt-3">← Back to Dashboard</a>

</div>

</body>
</html>
