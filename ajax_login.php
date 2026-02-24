<?php
header('Content-Type: application/json');

include 'includes/config.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = [
    'status' => 'error',
    'message' => 'An unexpected error occurred.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response['message'] = 'Please enter both email address and password.';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, email, password, role, is_verified FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 0) {
                $_SESSION['temp_user_id'] = $user['id']; // Store for verification
                $response['status'] = 'unverified';
                $response['message'] = 'Please verify your email address to continue. An OTP has been sent.';
                
                // Regenerate OTP if unverified and trying to login
                $otp = rand(100000, 999999);
                $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                $update = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE id = ?");
                $update->bind_param("ssi", $otp, $expiry, $user['id']);
                $update->execute();
                
                include 'includes/mailer.php';
                sendOTP($user['email'], $otp);

                echo json_encode($response);
                exit;
            }

            // Direct login for verified users
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role']  = $user['role'];

            $response['status'] = 'success';
            $response['message'] = 'Login successful! Redirecting...';
            $response['redirect'] = 'index.php'; // Or wherever the user should go

            // Rehash password if needed
            if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $updateHash = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $updateHash->bind_param("si", $newHash, $user['id']);
                $updateHash->execute();
            }

            echo json_encode($response);
            exit;

        } else {
            $response['message'] = 'Incorrect password.';
        }
    } else {
        $response['message'] = 'Email address not found.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
exit;
