<?php
session_start();
include '../includes/config.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Delete product image first
    $stmt = $conn->prepare("SELECT image FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $product = $res->fetch_assoc();
    $stmt->close();

    if ($product) {
        // Delete product image first if it's a local file
        if (!empty($product['image']) && strpos($product['image'], 'http') !== 0) {
            $imagePath = "../assets/images/" . $product['image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        // Delete product from table
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

header("Location: manage_products.php?category=" . urlencode($_GET['category'] ?? ''));
exit;
?>
