<?php
include 'includes/config.php';

echo "<h2>OTP System Migration</h2>";

// Columns to add
$columns = [
    'otp' => "VARCHAR(6) DEFAULT NULL AFTER role",
    'otp_expiry' => "DATETIME DEFAULT NULL AFTER otp",
    'is_verified' => "TINYINT(1) DEFAULT 0 AFTER otp_expiry"
];

foreach ($columns as $column => $definition) {
    $check = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
    if ($check->num_rows == 0) {
        $sql = "ALTER TABLE users ADD COLUMN $column $definition";
        if ($conn->query($sql)) {
            echo "<p style='color:green;'>SUCCESS: Added '$column' column to 'users' table.</p>";
        } else {
            echo "<p style='color:red;'>ERROR: Could not add $column column: " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color:blue;'>INFO: '$column' column already exists.</p>";
    }
}

echo "<br><a href='index.php'>Go to Home</a>";
?>
