<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../ajax_login.php");
    exit;
}
require_once '../includes/config.php';

// Fetch subscribers
$result = $conn->query("SELECT * FROM subscribers ORDER BY subscribed_at DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Subscribers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            background: #f8f9fa;
        }

        .main {
            flex: 1;
            padding: 30px;
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
            <h2>Newsletter Subscribers</h2>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#offerModal">
                    <i class="bi bi-megaphone-fill me-2"></i> Send Offer Email
                </button>
                <span class="badge bg-secondary fs-6 ms-2">
                    <?= $result->num_rows ?> Total
                </span>
            </div>
        </div>

        <div class="card p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Email Address</th>
                            <th>Subscribed On</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($row['subscribed_at'])) ?></td>
                                <td class="text-end">
                                    <a href="delete_subscriber.php?id=<?= $row['id'] ?>"
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to remove this subscriber?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($result->num_rows === 0): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No subscribers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Offer Modal -->
    <div class="modal fade" id="offerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Broadcast Offer to Subscribers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="offerForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Email Subject</label>
                            <input type="text" name="subject" class="form-control"
                                placeholder="e.g. Exclusive Weekend Sale!" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Offer Details</label>
                            <textarea name="message" class="form-control" rows="5"
                                placeholder="Type your offer content here..." required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Offer Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                            <div class="form-text">This image will be embedded in the email.</div>
                        </div>
                        <div id="broadcastStatus" class="alert d-none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="broadcastBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="loader"></span>
                            Send to All Subscribers
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('offerForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const btn = document.getElementById('broadcastBtn');
            const loader = document.getElementById('loader');
            const status = document.getElementById('broadcastStatus');
            const formData = new FormData(this);

            btn.disabled = true;
            loader.classList.remove('d-none');
            status.classList.add('d-none');

            fetch('ajax_broadcast_offer.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    status.innerText = data.message;
                    status.classList.remove('d-none', 'alert-success', 'alert-danger');
                    status.classList.add(data.status === 'success' ? 'alert-success' : 'alert-danger');

                    if (data.status === 'success') {
                        this.reset();
                        // Optionally close modal after delay
                        // setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('offerModal')).hide(), 2000);
                    }
                })
                .catch(error => {
                    status.innerText = 'An unexpected error occurred.';
                    status.classList.remove('d-none', 'alert-success', 'alert-danger');
                    status.classList.add('alert-danger');
                })
                .finally(() => {
                    btn.disabled = false;
                    loader.classList.add('d-none');
                });
        });
    </script>
</body>

</html>