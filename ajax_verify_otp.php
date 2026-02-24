<?php
header('Content-Type: application/json');
include 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['status' => 'error', 'message' => 'Invalid request.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');
    $user_id = $_SESSION['temp_user_id'] ?? null;

    if (!$user_id) {
        $response['message'] = 'Session expired. Please register again.';
    } elseif (strlen($otp) !== 6) {
        $response['message'] = 'Please enter a valid 6-digit OTP.';
    } else {
        // Fetch user OTP and expiry
        $stmt = $conn->prepare("SELECT name, email, role, otp, otp_expiry FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $current_time = date('Y-m-d H:i:s');

            if ($user['otp'] !== $otp) {
                $response['message'] = 'Incorrect OTP. Please try again.';
            } elseif ($user['otp_expiry'] < $current_time) {
                $response['message'] = 'OTP has expired. Please request a new one.';
            } else {
                // Success - Verify user
                $update = $conn->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
                $update->bind_param("i", $user_id);
                
                if ($update->execute()) {
                    $auto_login = ($_POST['auto_login'] ?? '1') === '1';
                    
                    if ($auto_login) {
                        $_SESSION['user_id']    = $user_id;
                        $_SESSION['user_name']  = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role']  = $user['role'];
                    }
                    
                    unset($_SESSION['temp_user_id']); // Clear temp ID

                    $response['status'] = 'success';
                    $response['message'] = $auto_login ? 'Verification successful! Logging you in...' : 'Verification successful! Please login to continue.';
                } else {
                    $response['message'] = 'Database error. Please try again later.';
                }
            }
        } else {
            $response['message'] = 'User not found.';
        }
    }
}

echo json_encode($response);
exit;
?>
