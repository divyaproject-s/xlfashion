<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $messageBody = trim($_POST['message'] ?? '');
    $imageFile = $_FILES['image'] ?? null;

    if (empty($subject) || empty($messageBody) || !$imageFile) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all fields and upload an image.']);
        exit;
    }

    // 1. Handle Image Upload
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed_ext)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid image format.']);
        exit;
    }

    $uniqueImageName = time() . "_" . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $imageFile['name']);
    $uploadPath = "../assets/images/offers/" . $uniqueImageName;

    if (!move_uploaded_file($imageFile['tmp_name'], $uploadPath)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']);
        exit;
    }

    // Full URL for the image to be used in email
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $imageUrl = $protocol . "://" . $host . "/TechSphere/xlfashion/assets/images/offers/" . $uniqueImageName;

    // 2. Fetch all subscribers
    $result = $conn->query("SELECT email FROM subscribers");
    $emails = [];
    while ($row = $result->fetch_assoc()) {
        $emails[] = $row['email'];
    }

    if (empty($emails)) {
        echo json_encode(['status' => 'error', 'message' => 'No subscribers found.']);
        exit;
    }

    // 3. Send Email using PHPMailer
    $mail = new PHPMailer(true);
    $successCount = 0;
    $errorCount = 0;

    try {
        // Server settings
        if (defined('SMTP_HOST') && SMTP_HOST !== 'smtp.example.com' && defined('SMTP_USER')) {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
        } else {
            // Fallback to mail() if SMTP not configured
            $mail->isMail();
        }

        $mail->setFrom('no-reply@xlfashion.com', 'XL Fashion Offers');

        // Embed the image
        $mail->addEmbeddedImage($uploadPath, 'offer_image');

        // Email Content Template
        $htmlContent = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                <div style='background: linear-gradient(135deg, #ff1493 0%, #ff69b4 100%); padding: 20px; text-align: center; color: white;'>
                    <h1 style='margin: 0;'>XL FASHION</h1>
                </div>
                <div style='padding: 20px;'>
                    <h2 style='color: #ff1493;'>$subject</h2>
                    <p style='line-height: 1.6; color: #444;'>$messageBody</p>
                    <div style='text-align: center; margin: 20px 0;'>
                        <img src='cid:offer_image' alt='Special Offer' style='max-width: 100%; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);'>
                    </div>
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='https://xlfashiontrends.sg/' style='background: #ff1493; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;'>Shop Now</a>
                    </div>
                </div>
                <div style='background: #f4f4f4; padding: 15px; text-align: center; color: #888; font-size: 12px;'>
                    <p>&copy; " . date('Y') . " XL Fashion. All rights reserved.</p>
                    <p>You received this email because you subscribed to our newsletter.</p>
                </div>
            </div>
        ";

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlContent;
        $mail->AltBody = "$messageBody";

        $lastError = '';
        foreach ($emails as $toEmail) {
            try {
                $mail->clearAddresses();
                $mail->addAddress($toEmail);
                if ($mail->send()) {
                    $successCount++;
                }
            } catch (Exception $e) {
                $errorCount++;
                $lastError = $mail->ErrorInfo;
            }
        }

        $resMsg = "Offer broadcasted! Success: $successCount, Errors: $errorCount";
        if ($successCount === 0 && $errorCount > 0) {
            $resMsg .= " — Details: " . $lastError;
        }

        echo json_encode([
            'status' => 'success',
            'message' => $resMsg
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Mailer Error: " . $mail->ErrorInfo]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>