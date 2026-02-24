<?php
include 'includes/config.php';

// Create offers table if not exists
$sql = "CREATE TABLE IF NOT EXISTS offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    discount_text VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    background_color VARCHAR(50) DEFAULT '#ff1493',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'offers' created successfully.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Insert dummy data if empty
$check = $conn->query("SELECT * FROM offers");
if ($check->num_rows == 0) {
    $insert = "INSERT INTO offers (title, description, discount_text, status, background_color) VALUES 
    ('Flash Sale', 'Get amazing discounts on our latest arrival of Kurtas and Sarees.', 'Flat 20% OFF', 'active', '#ff1493'),
    ('Weekend Bonanza', 'Buy 2 Get 1 Free on all Accessories.', 'Limited Time', 'active', '#ffd700')";
    
    if ($conn->query($insert) === TRUE) {
        echo "Dummy offers inserted successfully.<br>";
    } else {
        echo "Error inserting data: " . $conn->error . "<br>";
    }
} else {
    echo "Offers already exist.<br>";
}

echo "Setup Complete.";
?>
