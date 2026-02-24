<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../includes/config.php';

// Fetch all offers
$offers = $conn->query("SELECT * FROM offers ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Offers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; display: flex; min-height: 100vh; }
        .sidebar { width: 220px; background: #1c1e21; color: #fff; }
        .sidebar a { color: #fff; text-decoration: none; display: block; padding: 15px 20px; }
        .sidebar a:hover { background: #343a40; }
        .main { flex: 1; padding: 30px; }
        .card { border-radius: 12px; }
        .badge-active { background: #28a745; }
        .badge-inactive { background: #dc3545; }
    </style>
</head>
<body>

    <div class="main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🎁 Manage Homepage Offers</h2>
            <div>
                <a href="admin_index.php" class="btn btn-outline-secondary rounded-pill px-4 me-2">⬅ Back to Dashboard</a>
                <a href="add_offer.php" class="btn btn-primary rounded-pill px-4">➕ Add New Offer</a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Title</th>
                                <th>Discount Text</th>
                                <th>Link URL</th>
                                <th>Status</th>
                                <th>Background</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($offers->num_rows > 0): ?>
                                <?php while($row = $offers->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($row['title']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars(substr($row['description'], 0, 50)) ?>...</small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['discount_text']) ?></span></td>
                                        <td>
                                            <?php if (!empty($row['link_url'])): ?>
                                                <a href="<?= htmlspecialchars($row['link_url']) ?>" class="text-primary text-decoration-none" target="_blank">
                                                    <i class="bi bi-link-45deg"></i> <?= htmlspecialchars(substr($row['link_url'], 0, 30)) ?><?= strlen($row['link_url']) > 30 ? '...' : '' ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted"><i>No link</i></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="toggle_offer.php?id=<?= $row['id'] ?>&status=<?= $row['status'] ?>" 
                                               class="badge <?= $row['status'] == 'active' ? 'badge-active' : 'badge-inactive' ?> text-decoration-none">
                                                <?= ucfirst($row['status']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div style="width:20px; height:20px; background:<?= $row['background_color'] ?>; border-radius:50%; display:inline-block; vertical-align:middle;"></div>
                                            <?= $row['background_color'] ?>
                                        </td>
                                        <td>
                                            <a href="edit_offer.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete_offer.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this offer?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No offers found. Add one to show on homepage!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
