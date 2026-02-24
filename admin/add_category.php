<!-- PHP LOGIC (UNCHANGED) -->

<?php
session_start();
include '../includes/config.php';

// Admin check
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $msg = "Category name cannot be empty.";
    } else {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        $stmt = $conn->prepare("SELECT id FROM categories WHERE name=?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $msg = "Category name already exists.";
        }
        $stmt->close();
    }

    $image_name = NULL;
    $image_url = trim($_POST['image_url'] ?? '');

    if (empty($msg)) {
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $msg = "Only JPG, PNG, GIF, WEBP images are allowed.";
            } else {
                $image_name = time() . "_" . uniqid() . "." . $ext;
                $target = "../assets/category/" . $image_name;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $msg = "Failed to upload image.";
                }
            }
        }
    }

    if (empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, image, image_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $slug, $image_name, $image_url);
        if ($stmt->execute()) {
            header("Location: manage_categories.php");
            exit();
        } else {
            $msg = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>

<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Category</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f4f6f9; }
.card { border-radius:16px; box-shadow:0 10px 25px rgba(0,0,0,0.08); }
.card-header { background:linear-gradient(135deg,#6a1b9a,#8e24aa); color:#fff; border-radius:16px 16px 0 0; }
.form-control { border-radius:10px; }
.btn-primary { background:#6a1b9a; border:none; }
.btn-primary:hover { background:#4a148c; }
.preview-img { width:120px; height:120px; object-fit:cover; border-radius:12px; border:1px dashed #ccc; display:none; }
</style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">➕ Add New Category</h4>
                    <small>Manage your product categories</small>
                </div>
                <div class="card-body p-4">

                <?php if ($msg): ?>
                    <div class="alert alert-danger text-center">
                        <?= htmlspecialchars($msg) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Eg: Gowns, Sarees" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Category Image</label>
                        <div class="card p-3 border-light bg-light">
                            <div class="mb-2">
                                <label class="form-label small text-muted">Upload File</label>
                                <input type="file" name="image" class="form-control" onchange="previewImage(event)">
                            </div>
                            <div class="text-center my-1 fw-bold text-muted small">OR</div>
                            <div>
                                <label class="form-label small text-muted">Paste Image URL</label>
                                <input type="text" name="image_url" class="form-control" placeholder="https://example.com/item.jpg">
                            </div>
                        </div>
                        <img id="preview" class="preview-img mt-3" />
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="manage_categories.php" class="btn btn-outline-secondary">⬅ Back</a>
                        <button type="submit" class="btn btn-primary px-4">Save Category</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

</div>

<script>
function previewImage(event) {
    const img = document.getElementById('preview');
    img.src = URL.createObjectURL(event.target.files[0]);
    img.style.display = 'block';
}
</script>

</body>
</html>
