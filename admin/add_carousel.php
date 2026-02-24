<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $link = trim($_POST['link']);

    $image_name = "";
    $image_url = trim($_POST['image_url'] ?? '');

    if (empty($msg)) {
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $msg = "Only JPG, PNG, WEBP, GIF images are allowed.";
            } else {
                $image_name = "slide_" . time() . "." . $ext;
                $target = "../assets/carousel/" . $image_name;

                if (!is_dir('../assets/carousel')) {
                    mkdir('../assets/carousel', 0777, true);
                }

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $msg = "Failed to upload image.";
                }
            }
        }

        if (empty($msg)) {
            if (empty($image_name) && empty($image_url)) {
                $msg = "Please select an image file or provide an Image URL.";
            } else {
                $stmt = $conn->prepare("INSERT INTO carousel_images (image_path, image_url, title, link) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $image_name, $image_url, $title, $link);
                if ($stmt->execute()) {
                    header("Location: carousel.php");
                    exit;
                } else {
                    $msg = "Database Error: " . $conn->error;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Carousel Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .preview-box {
            border: 2px dashed #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            margin-top: 10px;
        }

        .preview-box img {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            display: none;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">➕ Add Carousel Image</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($msg): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($msg) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Carousel Image</label>
                                <div class="card p-3 border-light bg-light">
                                    <div class="mb-2">
                                        <label class="form-label small text-muted">Upload File</label>
                                        <input type="file" name="image" class="form-control" onchange="previewImage(event)">
                                    </div>
                                    <div class="text-center my-1 fw-bold text-muted small">OR</div>
                                    <div>
                                        <label class="form-label small text-muted">Paste Image URL</label>
                                        <input type="text" name="image_url" id="image_url" class="form-control" placeholder="https://example.com/slide.jpg" oninput="previewUrl(this.value)">
                                    </div>
                                </div>
                                <div class="preview-box">
                                    <span class="text-muted" id="placeholder">Image Preview</span>
                                    <img id="preview" src="" alt="Preview">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Title (Optional)</label>
                                <input type="text" name="title" class="form-control" placeholder="E.g. New Collection">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Link URL (Optional)</label>
                                <input type="text" name="link" class="form-control"
                                    placeholder="E.g. category.php?cat=Sarees">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Save Slide</button>
                                <a href="carousel.php" class="btn btn-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                const preview = document.getElementById('preview');
                const placeholder = document.getElementById('placeholder');
                preview.src = reader.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
                document.getElementById('image_url').value = ''; // Clear URL if file selected
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        function previewUrl(url) {
            const preview = document.getElementById('preview');
            const placeholder = document.getElementById('placeholder');
            if (url) {
                preview.src = url;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            } else {
                preview.style.display = 'none';
                placeholder.style.display = 'block';
            }
        }
    </script>
</body>

</html>