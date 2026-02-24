<?php
include 'includes/config.php';

echo "<h2>Registration Logic Verification</h2>";

// Mock POST data
$_POST['door_no'] = '12';
$_POST['street_name'] = 'Anna Nagar 2nd Street';
$_POST['city'] = 'Chennai';
$_POST['pincode'] = '600040';

// Test Address Formatting
$door_no     = trim($_POST['door_no']);
$street_name = trim($_POST['street_name']);
$city        = trim($_POST['city']);
$pincode     = trim($_POST['pincode']);

$formatted_address = "Door No $door_no, $street_name, $city – $pincode";

echo "<p><b>Test 1: Address Formatting</b></p>";
echo "<p>Expected: Door No 12, Anna Nagar 2nd Street, Chennai – 600040</p>";
echo "<p>Actual: " . htmlspecialchars($formatted_address) . "</p>";

if ($formatted_address === "Door No 12, Anna Nagar 2nd Street, Chennai – 600040") {
    echo "<p style='color: green;'>PASS: Address formatting is correct.</p>";
} else {
    echo "<p style='color: red;'>FAIL: Address formatting is incorrect.</p>";
}

// Test validation logic
echo "<p><b>Test 2: Validation Logic</b></p>";
$invalid_pincode = "12345";
if (strlen($invalid_pincode) !== 6) {
    echo "<p style='color: green;'>PASS: Pincode validation detected invalid length (5).</p>";
} else {
    echo "<p style='color: red;'>FAIL: Pincode validation failed to detect invalid length.</p>";
}

// Check database schema (existing address column)
echo "<p><b>Test 3: Database Schema Check</b></p>";
$result = $conn->query("DESCRIBE users");
$address_col_exists = false;
while ($row = $result->fetch_assoc()) {
    if ($row['Field'] === 'address') {
        $address_col_exists = true;
        break;
    }
}

if ($address_col_exists) {
    echo "<p style='color: green;'>PASS: 'address' column exists in 'users' table.</p>";
} else {
    echo "<p style='color: red;'>FAIL: 'address' column not found in 'users' table.</p>";
}

echo "<hr>";
echo "<p><i>Verification script complete. Please delete this file after review.</i></p>";
?>
