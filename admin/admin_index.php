<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../ajax_login.php");
    exit;
}
include '../includes/config.php';

// Fetch summary data
$stmt = $conn->prepare("SELECT IFNULL(SUM(total_amount),0) as total_sales FROM orders WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$totalSales = $stmt->get_result()->fetch_assoc()['total_sales'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$totalOrders = $stmt->get_result()->fetch_assoc()['total_orders'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total_products FROM products");
$stmt->execute();
$totalProducts = $stmt->get_result()->fetch_assoc()['total_products'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$totalUsers = $stmt->get_result()->fetch_assoc()['total_users'];
$stmt->close();

// Product categories
// Product categories
$categoryData = [];
$result = $conn->query("
    SELECT c.name AS category_name, COUNT(*) AS count 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    GROUP BY c.name
");
while ($row = $result->fetch_assoc()) {
    $categoryData[$row['category_name']] = $row['count'];
}


// Orders by status
$orderStatusData = [];
$result = $conn->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
while ($row = $result->fetch_assoc()) {
    $orderStatusData[$row['status']] = $row['count'];
}

// Recent orders
$recentOrders = $conn->query("SELECT o.id, o.total_amount, o.status, o.created_at, u.name
                              FROM orders o
                              JOIN users u ON o.user_id = u.id
                              ORDER BY o.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .table th,
        .table td {
            vertical-align: middle;
        }

        .btn-dashboard {
            border-radius: 25px;
            font-weight: 500;
            padding: 8px 20px;
        }
    </style>
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <h2 class="mb-4">Dashboard</h2>

        <!-- Summary Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm p-4 text-white bg-primary">
                    <h5>Total Sales Today</h5>
                    <h3>₹<?= number_format($totalSales, 2) ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm p-4 text-white bg-success">
                    <h5>Total Orders Today</h5>
                    <h3><?= $totalOrders ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm p-4 text-dark bg-warning">
                    <h5>Total Products</h5>
                    <h3><?= $totalProducts ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm p-4 text-white bg-info">
                    <h5>Total Users</h5>
                    <h3><?= $totalUsers ?></h3>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <h5 class="mb-3">Product Categories</h5>
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <h5 class="mb-3">Orders by Status</h5>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card shadow-sm p-4">
            <div class="d-flex justify-content-between mb-3">
                <h5>Recent Orders</h5>
                <a href="manage_orders.php" class="btn btn-primary btn-dashboard">View All Orders</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Total (₹)</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= number_format($row['total_amount'], 2) ?></td>
                                <td>
                                    <span
                                        class="badge bg-<?= $row['status'] == 'Completed' ? 'success' : ($row['status'] == 'Pending' ? 'warning' : 'secondary') ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td><?= $row['created_at'] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const ctx1 = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_keys($categoryData)) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($categoryData)) ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF']
                }]
            }
        });

        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($orderStatusData)) ?>,
                datasets: [{
                    label: 'Orders',
                    data: <?= json_encode(array_values($orderStatusData)) ?>,
                    backgroundColor: '#36A2EB'
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>

</html>