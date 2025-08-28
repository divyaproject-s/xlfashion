<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../includes/config.php';

// Fetch all users
$sql = "SELECT id, name, email FROM users ORDER BY id DESC";
$users = $conn->query($sql);

// Fetch all admins
$sql_admins = "SELECT id, username FROM admin ORDER BY id DESC";
$admins = $conn->query($sql_admins);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="bg-white p-4 rounded shadow">
        <h2 class="mb-4">👤 Manage Users</h2>

        <!-- Customers Table -->
        <h4>Customers</h4>
        <table class="table table-striped align-middle mb-4">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Admins Table -->
        <h4>Admins</h4>
        <table class="table table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $admins->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="mt-3">
            <a href="add_admin.php" class="btn btn-primary">➕ Add New Admin</a>
            <a href="admin_index.php" class="btn btn-outline-secondary">⬅ Back to Dashboard</a>
        </div>
    </div>
</div>
</body>
</html>
