<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    die("Unauthorized");
}
include '../includes/config.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $new_status = ($_GET['status'] == 'active') ? 'inactive' : 'active';
    
    $stmt = $conn->prepare("UPDATE offers SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
}

header("Location: manage_offers.php");
exit;
?>
