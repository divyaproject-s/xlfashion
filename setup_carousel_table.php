<?php
include 'includes/config.php';

$sql = "CREATE TABLE IF NOT EXISTS carousel_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    title VARCHAR(100) DEFAULT NULL,
    link VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";

if ($conn->query($sql) === TRUE) {
    echo "<h1>Success!</h1><p>Table 'carousel_images' created successfully.</p>";
    echo "<p><a href='index.php'>Go back to Home</a></p>";
} else {
    echo "<h1>Error</h1><p>Error creating table: " . $conn->error . "</p>";
}

$conn->close();
?>