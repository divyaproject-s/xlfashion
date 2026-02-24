<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../includes/config.php';

// Fetch all curated looks
$looks = $conn->query("SELECT * FROM curated_looks ORDER BY display_order ASC, id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Curated Looks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
        .look-thumb {
            width: 70px;
            height: 90px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        .table th {
            background: #1c1e21 !important;
            color: #fff !important;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">✨ Manage Curated Looks</h2>
            <a href="add_curated_look.php" class="btn btn-primary px-4">➕ Add New Look</a>
        </div>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['msg']; unset($_SESSION['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Image</th>
                                <th>Title</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($looks->num_rows > 0): ?>
                                <?php while($row = $looks->fetch_assoc()): ?>
                                    <tr>
                                        <td class="ps-4 text-center">
                                        <?php
                                        $img_path = $row['image_path'];
                                        $img_url = $row['image_url'] ?? '';
                                        $img_src = !empty($img_url) ? $img_url : (!empty($img_path) ? "../assets/curated/" . $img_path : "");
                                        ?>
                                        <img src="<?= htmlspecialchars($img_src) ?>"
                                             class="rounded shadow-sm" style="width: 70px; height: 90px; object-fit: cover;">
                                    </td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($row['title']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($row['link']) ?></small>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] == 'active'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <a href="toggle_curated_look.php?id=<?= $row['id'] ?>&status=<?= $row['status'] ?>" 
                                                   class="btn btn-sm <?= $row['status'] == 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                                    <?= $row['status'] == 'active' ? 'Disable' : 'Enable' ?>
                                                </a>
                                                <a href="edit_curated_look.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </a>
                                                <a href="delete_curated_look.php?id=<?= $row['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Confirm delete this curated look?')">
                                                    <i class="bi bi-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">No curated looks found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
