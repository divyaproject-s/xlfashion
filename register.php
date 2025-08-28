<?php
include 'includes/config.php';
include 'includes/header.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to homepage
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Handle form submission
if (isset($_POST['register'])) {
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm  = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Check password match
    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check_email = $conn->query("SELECT id FROM users WHERE email='$email' LIMIT 1");
        if ($check_email->num_rows > 0) {
            $error = "Email is already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into database
            if ($conn->query("INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')")) {
                $success = "Registration successful! <a href='login.php'>Click here to login</a>";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<style>
    /* Make body full height and flex column */
    body {
        font-family: Arial, sans-serif;
        background: #f3f4f6;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        margin: 0;
    }

    /* Content takes remaining space */
    .content {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .register-container {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 400px;
    }
    .register-container h2 {
        margin-bottom: 20px;
        text-align: center;
        color: #333;
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
        color: #444;
    }
    input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
    }
    button {
        width: 100%;
        padding: 12px;
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    button:hover {
        background: #45a049;
    }
    .message {
        text-align: center;
        margin-bottom: 15px;
        font-size: 14px;
    }
    .message.error {
        color: red;
    }
    .message.success {
        color: green;
    }
    p {
        text-align: center;
        margin-top: 15px;
        font-size: 14px;
    }
    a {
        color: #4CAF50;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
    /* Footer style */
    footer {
        background: #222;
        color: #fff;
        text-align: center;
        padding: 15px;
        margin-top: auto;
    }
</style>

<div class="content">
    <div class="register-container">
        <h2>Create an Account</h2>

        <?php if (isset($error)): ?>
            <p class="message error"><?= $error ?></p>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <p class="message success"><?= $success ?></p>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" name="register">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
