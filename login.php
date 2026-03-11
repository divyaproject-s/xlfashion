<?php
session_start();
include 'includes/config.php';
include 'includes/mailer.php';

$message = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            if ($user['is_verified'] == 0) {
                $_SESSION['temp_user_id'] = $user['id'];
                header("Location: verify_otp.php?msg=Please verify your email");
                exit;
            }

            // Direct login for verified users
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'] ?? 'User';
            $_SESSION['user_email'] = $email;

            header("Location: index.php");
            exit;
        } else {
            $message = "Invalid password!";
        }
    } else {
        $message = "Account not found!";
    }
}
include 'includes/header.php';
?>
<main class="py-5">
    <div class="container">
        <div class="card auth-card p-4">
            <h3 class="text-center fw-bold mb-4">Login</h3>
            <?php if ($message): ?>
                <div class="alert alert-danger fsm-sm py-2 px-3"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="login" class="btn btn-primary w-100 py-2">Login</button>
                <p class="text-center mt-3 small">New here? <a href="register.php">Register</a></p>
            </form>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>