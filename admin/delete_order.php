<?php
session_start();
include '../includes/config.php'; // Your DB connection

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['id'])) {
    $order_id = intval($_POST['id']); // Always sanitize input

    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        $_SESSION['msg'] = "<p class='text-success'>Order #$order_id deleted successfully</p>";
    } else {
        $_SESSION['msg'] = "<p class='text-danger'>Failed to delete order #$order_id</p>";
    }

    $stmt->close();
    header("Location: manage_orders.php");
    exit;
} else {
    $_SESSION['msg'] = "<p class='text-danger'>Invalid request</p>";
    header("Location: manage_orders.php");
    exit;
}
?>
