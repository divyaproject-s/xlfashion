<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../includes/config.php';

$msg = "";
$type = "";

if (!isset($_GET['id'])) {
    header("Location: manage_offers.php");
    exit;
}

$id = intval($_GET['id']);
$offer_res = $conn->query("SELECT * FROM offers WHERE id = $id");
if ($offer_res->num_rows == 0) {
    header("Location: manage_offers.php");
    exit;
}
$offer = $offer_res->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $discount_text = $_POST['discount_text'];
    $bg_color = $_POST['background_color'];
    $link_url = trim($_POST['link_url'] ?? '');

    $stmt = $conn->prepare("UPDATE offers SET title = ?, description = ?, discount_text = ?, background_color = ?, link_url = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $title, $description, $discount_text, $bg_color, $link_url, $id);
    
    if ($stmt->execute()) {
        header("Location: manage_offers.php");
        exit;
    } else {
        $msg = "Error: " . $conn->error;
        $type = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Offer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-primary text-white p-4 rounded-top-4">
                        <h4 class="mb-0">✏️ Edit Offer</h4>
                        <small>Update the promotional details for this offer</small>
                    </div>
                    <div class="card-body p-4">
                        <?php if($msg): ?>
                            <div class="alert alert-<?= $type ?>"><?= $msg ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Offer Title</label>
                                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($offer['title']) ?>" placeholder="e.g. Festival Special" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="3" placeholder="Describe the offer details..." required><?= htmlspecialchars($offer['description']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Discount Tag</label>
                                <input type="text" name="discount_text" class="form-control" value="<?= htmlspecialchars($offer['discount_text']) ?>" placeholder="e.g. 50% OFF or Buy 2 Get 1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Link URL (Optional)</label>
                                <input type="text" name="link_url" class="form-control" value="<?= htmlspecialchars($offer['link_url'] ?? '') ?>" placeholder="e.g. category.php?cat=sarees or product.php?id=5">
                                <small class="text-muted">Leave empty if offer should not be clickable</small>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">Background Color</label>
                                <div class="d-flex gap-3 align-items-center">
                                    <input type="color" name="background_color" class="form-control form-control-color" value="<?= htmlspecialchars($offer['background_color']) ?>" title="Choose background color">
                                    <small class="text-muted">Choose a color that matches your theme</small>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="manage_offers.php" class="btn btn-outline-secondary px-4 rounded-pill">Cancel</a>
                                <button type="submit" class="btn btn-primary px-5 rounded-pill fw-bold">Update Offer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
