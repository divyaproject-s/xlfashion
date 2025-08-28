<?php
session_start();

// Secure admin session check
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy: default-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="bg-white p-4 rounded shadow">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
            <div>
                <span class="me-3">
                    <i class="bi bi-person-circle"></i>
                    <?= htmlspecialchars($_SESSION['user_name']) ?> (<?= htmlspecialchars($_SESSION['user_role']) ?>)
                </span>
                <a href="../logout.php" class="btn btn-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>

        <p class="text-muted">Welcome to the admin panel. Use the menu below to manage your store.</p>

        <div class="row g-4 mt-3">
            <!-- Products -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center">
                        <i class="bi bi-box-seam display-5 text-primary"></i>
                        <h5 class="card-title mt-3">Products</h5>
                        <p class="text-muted">Add, update, or remove products.</p>
                        <div class="d-grid gap-2">
                            <a href="add_product.php" class="btn btn-primary">➕ Add Product</a>
                            <a href="manage_products.php" class="btn btn-outline-primary">📦 Manage Products</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center">
                        <i class="bi bi-bag-check display-5 text-success"></i>
                        <h5 class="card-title mt-3">Orders</h5>
                        <p class="text-muted">View and manage customer orders.</p>
                        <a href="manage_orders.php" class="btn btn-success w-100">Manage Orders</a>
                    </div>
                </div>
            </div>

            <!-- Users -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body text-center">
                        <i class="bi bi-people display-5 text-warning"></i>
                        <h5 class="card-title mt-3">Users</h5>
                        <p class="text-muted">Manage registered users and their roles.</p>
                        <a href="manage_users.php" class="btn btn-warning w-100">Manage Users</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="../index.php" class="btn btn-outline-secondary">
                <i class="bi bi-house"></i> Back to Home
            </a>
        </div>
    </div>
</div>
</body>
</html>
