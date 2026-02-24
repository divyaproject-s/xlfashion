<?php
include 'includes/config.php';
$res = $conn->query("DESCRIBE products");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>