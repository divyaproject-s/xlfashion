<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_categories.php");
    exit();
}
$id = (int)$_GET['id'];

// Fetch category to delete image
$stmt = $conn->prepare("SELECT image FROM categories WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $cat = $result->fetch_assoc();

    // Delete image if it's a local file
    if (!empty($cat['image']) && strpos($cat['image'], 'http') !== 0) {
        $imagePath = "../assets/category/" . $cat['image'];
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }

    // Delete category
    $del_stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $del_stmt->bind_param("i", $id);
    $del_stmt->execute();
    $del_stmt->close();
}

$stmt->close();
header("Location: manage_categories.php");
exit();
?>
