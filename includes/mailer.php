<?php
// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Use the existing database connection from the file that includes this one
// (Usually config.php is already included in the parent script)
global $conn;

// Composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

define('SMTP_EMAIL', 'divyasubramanian1103@gmail.com');
define('SMTP_PASS', 'tztr lcxa qkrr hrea');

function sendOTP($email, $otp) {
    global $conn;
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_EMAIL;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom(SMTP_EMAIL, 'XL Fashion');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Your OTP Code - XL Fashion";
        $mail->Body    = "<h2>Your OTP: $otp</h2><p>This code is valid for 10 minutes.</p>";
        $mail->AltBody = "Your OTP: $otp. Valid for 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error if needed
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return "Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
