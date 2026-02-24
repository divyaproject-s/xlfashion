<?php
header('Content-Type: application/json');
include 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // STEP 1: SEND OTP
    if ($action === 'send_otp') {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Please enter a valid email address.';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                $otp = rand(100000, 999999);
                $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));

                // Store in DB
                $update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE id = ?");
                $update->bind_param("ssi", $otp, $expiry, $user['id']);
                
                if ($update->execute()) {
                    include 'includes/mailer.php';
                    if (sendOTP($email, $otp, 'Password Reset') === true) {
                        $_SESSION['reset_email'] = $email;
                        $_SESSION['otp_verified'] = false;

                        $response['status'] = 'success';
                        $response['message'] = 'OTP sent successfully to your email.';
                    } else {
                        $response['message'] = 'Failed to send OTP email. Please try again.';
                    }
                } else {
                    $response['message'] = 'Database error. Please try again.';
                }
            } else {
                $response['message'] = 'This email address is not registered.';
            }
        }
    }

    // STEP 2: VERIFY OTP
    elseif ($action === 'verify_otp') {
        $otp = trim($_POST['otp'] ?? '');
        $email = $_SESSION['reset_email'] ?? '';

        if (empty($email)) {
            $response['message'] = 'Session expired. Please try again.';
        } else {
            $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email = ? LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                $current_time = date('Y-m-d H:i:s');
                if ((string)$user['otp'] === (string)$otp && $user['otp_expiry'] >= $current_time) {
                    $_SESSION['otp_verified'] = true;
                    $response['status'] = 'success';
                    $response['message'] = 'OTP verified! You can now reset your password.';
                } else {
                    $response['message'] = 'Invalid or expired OTP. Please try again.';
                }
            } else {
                $response['message'] = 'User not found.';
            }
        }
    }

    // STEP 3: RESET PASSWORD
    elseif ($action === 'reset_password') {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
            $response['message'] = 'Unauthorized access. Please verify OTP first.';
        } elseif (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[^A-Za-z0-9]/', $new_password)) {
            $response['message'] = 'Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.';
        } elseif ($new_password !== $confirm_password) {
            $response['message'] = 'Passwords do not match.';
        } else {
            $email = $_SESSION['reset_email'];
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);

            if ($stmt->execute()) {
                // Clear reset session variables
                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_verified']);

                $response['status'] = 'success';
                $response['message'] = 'Password updated successfully! Redirecting to login...';
            } else {
                $response['message'] = 'Database update failed. Please try again.';
            }
        }
    }
}

echo json_encode($response);
exit;
