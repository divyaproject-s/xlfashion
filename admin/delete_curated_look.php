<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../includes/config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Get image path to delete file
    $res = $conn->query("SELECT image_path FROM curated_looks WHERE id = $id");
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['image_path']) && strpos($row['image_path'], 'http') !== 0) {
            $file = "../assets/curated/" . $row['image_path'];
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }
    
    $conn->query("DELETE FROM curated_looks WHERE id = $id");
}
header("Location: manage_curated_looks.php?msg=deleted");
exit;
