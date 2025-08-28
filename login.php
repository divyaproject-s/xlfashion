<?php
// ---------- Secure session settings ----------
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
          || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

ini_set('session.use_strict_mode', 1);
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $secure,     // true on HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
require_once __DIR__ . '/includes/config.php';

// ---------- Security headers ----------
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy: default-src 'self' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;");

// ---------- Redirect if already logged in ----------
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin/admin_index.php');
        exit;
    } else {
        header('Location: index.php');
        exit;
    }
}

// ---------- CSRF token ----------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ---------- Rate limiting ----------
$MAX_ATTEMPTS = 5;
$LOCK_SECONDS = 10 * 60;

if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['login_lock_until'])) $_SESSION['login_lock_until'] = 0;

$error = "";

// ---------- Handle login ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (time() < $_SESSION['login_lock_until']) {
        $error = "Too many failed attempts. Try again later.";
    } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Invalid request. Please refresh and try again.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $sql = "SELECT id, name, email, username, password, role
                FROM users
                WHERE LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?)
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Rehash password if needed
                if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                    $update->bind_param("si", $newHash, $user['id']);
                    $update->execute();
                    $update->close();
                }

                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'] ?? 'user';

                // reset attempts
                $_SESSION['login_attempts'] = 0;
                $_SESSION['login_lock_until'] = 0;

                $stmt->close();
                $result->free();

                if ($_SESSION['user_role'] === 'admin') {
                    header("Location: admin/admin_index.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            }
        }

        // failed login
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= $MAX_ATTEMPTS) {
            $_SESSION['login_lock_until'] = time() + $LOCK_SECONDS;
            $_SESSION['login_attempts'] = 0;
            $error = "Too many failed attempts. Please wait 10 minutes.";
        } else {
            $remaining = $MAX_ATTEMPTS - $_SESSION['login_attempts'];
            $error = "Invalid login credentials. You have {$remaining} attempts left.";
        }

        $stmt->close();
        $result?->free();
    }
}

// Show alert if redirected
$login_alert = $_SESSION['login_alert'] ?? null;
unset($_SESSION['login_alert']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Alankara</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="login-box bg-white p-4 shadow rounded mx-auto" style="max-width:400px">
      <h3 class="text-center mb-3"><i class="bi bi-box-arrow-in-right"></i> Login</h3>

      <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <?php if ($login_alert): ?>
        <div class="alert alert-warning text-center"><?= htmlspecialchars($login_alert) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="mb-3">
          <label class="form-label">Email or Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3 position-relative">
          <label class="form-label">Password</label>
          <div class="input-group">
            <input type="password" id="password" name="password" class="form-control" required>
            <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>

      <div class="text-center mt-3">
        <a href="forgot_password.php">Forgot password?</a> | <a href="index.php">← Back to Home</a>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const pwd = document.getElementById("password");
      const icon = event.currentTarget.querySelector("i");
      if (pwd.type === "password") {
        pwd.type = "text";
        icon.classList.replace("bi-eye", "bi-eye-slash");
      } else {
        pwd.type = "password";
        icon.classList.replace("bi-eye-slash", "bi-eye");
      }
    }
  </script>
</body>
</html>
