<?php
include 'includes/config.php';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Basic Validation
    if (empty($name) || empty($email) || empty($mobile) || empty($address)) {
        $error_msg = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $error_msg = "Please enter a valid 10-digit mobile number.";
    } else {
        // Update Query
        $sql = "UPDATE users SET name = '$name', email = '$email', mobile = '$mobile', address = '$address' WHERE id = '$user_id'";
        
        if ($conn->query($sql)) {
            $success_msg = "Profile updated successfully!";
            // Update sessions if changed
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
        } else {
            $error_msg = "Error updating profile: " . $conn->error;
        }
    }
}

// Fetch current user data
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    $error_msg = "User not found."; 
}

?>

<style>
    .profile-container {
        max_width: 800px;
        margin: 50px auto;
        padding: 20px;
    }
    .profile-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 40px 20px;
        text-align: center;
        color: white;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: bold;
        margin: 0 auto 15px;
        border: 4px solid rgba(255,255,255,0.3);
    }
    .profile-body {
        padding: 30px;
    }
    .form-group label {
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
    }
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(118, 75, 162, 0.25);
        border-color: #764ba2;
    }
    .btn-update {
        background: #764ba2;
        color: white;
        padding: 12px 30px;
        border-radius: 50px;
        border: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-update:hover {
        background: #5d3b82;
        transform: translateY(-2px);
    }
</style>

<div class="container profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <h2 class="mb-0"><?= htmlspecialchars($user['name']) ?></h2>
            <p class="mb-0 opacity-75"><?= htmlspecialchars($user['email'] ?? 'User') ?></p>
        </div>
        
        <div class="profile-body">
            
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="mobile" class="form-label">Mobile Number</label>
                            <input type="tel" class="form-control" id="mobile" name="mobile" 
                                   value="<?= htmlspecialchars($user['mobile']) ?>" 
                                   pattern="[0-9]{10}" title="Please enter valid 10 digit number" required>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-group">
                            <label for="address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                    </div>

                    <div class="col-12 text-center mt-4">
                        <button type="submit" name="update_profile" class="btn btn-update">
                            <i class="bi bi-save me-2"></i> Update Profile
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// Add some spacing before footer
echo '<div style="margin-bottom: 100px;"></div>';
include 'includes/footer.php'; 
?>
