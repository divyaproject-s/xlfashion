<?php
$host = "localhost";   // Database host
$user = "root";        // Database username
$pass = "";            // Database password
$dbname = "xlfashion_db";  // Database name

// Create connection
// $conn = new mysqli($host, $user, $pass, $dbname);
$conn = new mysqli("127.0.0.1","root","","xlfashion_db","3307");


// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// SMTP configuration for PHPMailer
// FILL THESE IN to enable email sending
if (!defined('SMTP_HOST'))
    define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_USER'))
    define('SMTP_USER', 'subaselvam298@gmail.com');
if (!defined('SMTP_PASS'))
    define('SMTP_PASS', 'zvyw ekvc rptg yxng');
if (!defined('SMTP_PORT'))
    define('SMTP_PORT', 587);
if (!defined('SMTP_SECURE'))
    define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
?>