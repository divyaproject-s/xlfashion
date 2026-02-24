<?php
session_start();
include 'includes/config.php';
include 'includes/mailer.php';

$message = "";
$status = "";

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if user exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $message = "Email already registered!";
        $status = "error";
    } else {
        $otp = random_int(100000, 999999);
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, otp, otp_expiry, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sssss", $name, $email, $password, $otp, $expiry);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            if (sendOTP($email, $otp, 'Registration Verification') === true) {
                $_SESSION['temp_user_id'] = $user_id;
                header("Location: verify_otp.php");
                exit;
            } else {
                $message = "Account created but failed to send email. Please contact support.";
                $status = "error";
            }
        } else {
            $message = "Registration failed!";
            $status = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - XL Fashion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .auth-card { max-width: 400px; margin: 80px auto; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container">
    <div class="card auth-card p-4">
        <h3 class="text-center fw-bold mb-4">Create Account</h3>
        <?php if($message): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="register" class="btn btn-primary w-100 py-2">Register</button>
            <p class="text-center mt-3 small">Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</div>
</body>
</html>
