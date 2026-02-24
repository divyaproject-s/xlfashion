<?php
header('Content-Type: application/json');
include 'includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = [
    'status' => 'error',
    'message' => 'Something went wrong.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name   = trim($_POST['name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');

    $door_no     = trim($_POST['door_no'] ?? '');
    $street_name = trim($_POST['street_name'] ?? '');
    $city        = trim($_POST['city'] ?? '');
    $pincode     = trim($_POST['pincode'] ?? '');

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $address = "Door No $door_no, $street_name, $city – $pincode";

    /* ---------------- VALIDATIONS ---------------- */

    if (
        empty($name) || empty($email) || empty($mobile) ||
        empty($door_no) || empty($street_name) ||
        empty($city) || empty($pincode) ||
        empty($password)
    ) {
        $response['message'] = "Please fill all required fields.";
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format.";
        echo json_encode($response);
        exit;
    }

    if ($password !== $confirm) {
        $response['message'] = "Passwords do not match.";
        echo json_encode($response);
        exit;
    }

    if (
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password) ||
        strlen($password) < 8
    ) {
        $response['message'] = "Password must contain uppercase, lowercase, number, special character and minimum 8 characters.";
        echo json_encode($response);
        exit;
    }

    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $response['message'] = "Mobile number must be exactly 10 digits.";
        echo json_encode($response);
        exit;
    }

    if (!preg_match('/^[0-9]{6}$/', $pincode)) {
        $response['message'] = "Pincode must be exactly 6 digits.";
        echo json_encode($response);
        exit;
    }

    /* ---------------- CHECK EXISTING USER ---------------- */

    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR mobile = ?");
    $check->bind_param("ss", $email, $mobile);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $response['message'] = "Email or Mobile already registered.";
        echo json_encode($response);
        exit;
    }

    /* ---------------- TRANSACTION START ---------------- */

    $conn->begin_transaction();

    try {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $role = 'user';

        // Generate OTP BEFORE insert
        $otp = rand(100000, 999999);
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Insert user WITH OTP directly
        $insert = $conn->prepare("
            INSERT INTO users 
            (name, email, mobile, address, password, role, otp, otp_expiry, is_verified) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)
        ");

        $insert->bind_param(
            "ssssssss",
            $name,
            $email,
            $mobile,
            $address,
            $hashed_password,
            $role,
            $otp,
            $expiry
        );

        if (!$insert->execute()) {
            throw new Exception("Insert failed: " . $insert->error);
        }

        $user_id = $conn->insert_id;

        // Send OTP Mail
        include 'includes/mailer.php';
        $mailed = sendOTP($email, $otp);

        if ($mailed !== true) {
            throw new Exception("Mail sending failed.");
        }

        // Commit transaction
        $conn->commit();

        $_SESSION['temp_user_id'] = $user_id;

        $response['status'] = 'success';
        $response['message'] = 'Registration successful! OTP sent to email.';

    } catch (Exception $e) {

        $conn->rollback();
        $response['message'] = "Registration failed: " . $e->getMessage();
    }

} else {
    $response['message'] = "Invalid request method.";
}

echo json_encode($response);
exit;