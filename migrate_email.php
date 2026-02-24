<?php
include 'includes/config.php';

echo "<h2>Database Migration</h2>";

// Check if email column exists
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'email'");
if ($check->num_rows == 0) {
    $sql = "ALTER TABLE users ADD COLUMN email VARCHAR(150) NOT NULL UNIQUE AFTER name";
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>SUCCESS: Added 'email' column to 'users' table.</p>";
    } else {
        echo "<p style='color:red;'>ERROR: Could not add email column: " . $conn->error . "</p>";
    }
} else {
    echo "<p style='color:blue;'>INFO: 'email' column already exists.</p>";
}

echo "<br><a href='index.php'>Go to Home</a>";
?>
